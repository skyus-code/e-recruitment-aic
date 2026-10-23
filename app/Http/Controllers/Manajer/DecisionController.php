<?php

namespace App\Http\Controllers\Manajer;

use App\Enums\ApplicationStatusEnum;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationStatusMail;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DecisionController extends Controller
{
    /**
     * Rekap keputusan akhir & log offering kandidat.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');

        $applications = Application::with(['user.detailPerson', 'job.department', 'interviewSchedule'])
            ->whereNotNull('score')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByDesc('score')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'hired' => Application::where('status', ApplicationStatusEnum::Hired)->count(),
            'rejected' => Application::where('status', ApplicationStatusEnum::Rejected)->count(),
            'pending' => Application::whereIn('status', [
                ApplicationStatusEnum::Applied,
                ApplicationStatusEnum::Screening,
                ApplicationStatusEnum::Passed,
                ApplicationStatusEnum::Interview,
            ])->count(),
        ];

        return view('manajer.decisions', compact('applications', 'stats', 'status'));
    }

    /**
     * Menetapkan keputusan akhir: HIRE atau REJECT.
     */
    public function update(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:hired,rejected'],
            'notes' => ['required', 'string', 'max:3000'],
        ], [
            'decision.required' => 'Keputusan wajib dipilih.',
            'decision.in' => 'Keputusan tidak dikenali.',
            'notes.required' => 'Catatan wawancara wajib diisi sebelum mengambil keputusan.',
            'notes.max' => 'Catatan wawancara maksimal 3000 karakter.',
        ]);

        $status = $validated['decision'] === 'hired'
            ? ApplicationStatusEnum::Hired
            : ApplicationStatusEnum::Rejected;

        $application->update(['status' => $status]);

        $schedule = $application->interviewSchedule()->first();

        $schedule?->update([
            'status' => $status->value,
            'notes' => $validated['notes'],
        ]);

        // Kirim email keputusan akhir (hired/rejected) ke pelamar.
        $applicant = $application->user;
        Mail::to($applicant->email)->send(
            new ApplicationStatusMail($applicant->name, $validated['decision']),
        );

        $message = $status === ApplicationStatusEnum::Hired
            ? 'Selamat! Kandidat dinyatakan DITERIMA (HIRE).'
            : 'Kandidat dinyatakan DITOLAK (REJECT).';

        return redirect()->route('manajer.interviews.show', $application)
            ->with('success', $message)
            ->with('message', 'Catatan wawancara tersimpan pada berkas kandidat.');
    }

    /**
     * Unduh rekap data satu pelamar (CSV) berisi identitas, formasi,
     * skor, jadwal wawancara, riwayat pendidikan, pengalaman & keahlian.
     */
    public function rekap(Application $application): StreamedResponse
    {
        $application->load([
            'user.detailPerson',
            'user.educations',
            'user.experiences',
            'user.skills',
            'job.department',
            'job.keywords',
            'interviewSchedule',
            'scoreDetails',
        ]);

        $user = $application->user;
        $detail = $user->detailPerson;
        $schedule = $application->interviewSchedule;
        $matchedKeywords = $application->scoreDetails->where('is_matched', true)->count();

        $filename = 'rekap-'.Str::slug($user->name).'-'.$application->id.'.csv';

        return response()->stream(function () use ($application, $user, $detail, $schedule, $matchedKeywords) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 agar dibaca rapi di Excel.
            fwrite($out, "\xEF\xBB\xBF");

            $section = fn (string $label) => fputcsv($out, [$label]);
            $row = fn (string $key, $value) => fputcsv($out, [$key, $value]);

            $section('REKAP PELAMAR — AR-RAHMAN ISLAMIC CENTER');
            fputcsv($out, []);

            $section('IDENTITAS');
            $row('Nama Lengkap', $user->name);
            $row('Email', $user->email);
            $row('Telepon', $user->phone ?? '—');
            $row('Jenis Kelamin', $detail?->gender ?? '—');
            $row('Tempat Lahir', $detail?->birth_place ?? '—');
            $row('Tanggal Lahir', $detail?->birth_date?->translatedFormat('d F Y') ?? '—');
            $row('Alamat', preg_replace('/\s+/', ' ', (string) ($detail?->full_address ?? '—')));
            fputcsv($out, []);

            $section('LAMARAN');
            $row('Formasi', $application->job->title);
            $row('Departemen', $application->job->department?->name ?? '—');
            $row('Tanggal Melamar', $application->applied_at?->translatedFormat('d F Y') ?? '—');
            $row('Status', $application->status->label());
            $row('Skor Kesesuaian CV', $application->score ?? '—');
            $row('Keyword Cocok', $matchedKeywords.'/'.$application->job->keywords->count());
            fputcsv($out, []);

            $section('JADWAL WAWANCARA');
            $row('Tanggal', $schedule?->interview_date?->translatedFormat('d F Y') ?? 'Belum dijadwalkan');
            $row('Waktu', $schedule?->interview_time ?? '—');
            $row('Lokasi Wawancara', $schedule ? ($schedule->interview_type === 'online' ? 'Online' : 'Offline') : '—');
            $row('Detail Lokasi', $schedule?->interview_location ?? '—');
            $row('Catatan Wawancara', preg_replace('/\s+/', ' ', (string) ($schedule?->notes ?? '—')));
            fputcsv($out, []);

            $section('RIWAYAT PENDIDIKAN');
            if ($user->educations->isEmpty()) {
                fputcsv($out, ['Belum ada data pendidikan']);
            } else {
                fputcsv($out, ['Institusi', 'Jenjang', 'Jurusan', 'Tahun', 'IPK']);
                foreach ($user->educations as $education) {
                    fputcsv($out, [
                        $education->institution,
                        $education->degree ?? '—',
                        $education->major ?? '—',
                        collect([$education->start_year, $education->end_year])->filter()->implode('-') ?: '—',
                        $education->gpa ?? '—',
                    ]);
                }
            }
            fputcsv($out, []);

            $section('PENGALAMAN KERJA');
            if ($user->experiences->isEmpty()) {
                fputcsv($out, ['Belum ada data pengalaman kerja']);
            } else {
                fputcsv($out, ['Posisi', 'Perusahaan', 'Mulai', 'Selesai', 'Keterangan']);
                foreach ($user->experiences as $experience) {
                    fputcsv($out, [
                        $experience->position,
                        $experience->company,
                        $experience->start_date?->format('M Y') ?? '—',
                        $experience->end_date?->format('M Y') ?? 'Sekarang',
                        preg_replace('/\s+/', ' ', (string) ($experience->description ?? '')),
                    ]);
                }
            }
            fputcsv($out, []);

            $section('KEAHLIAN');
            if ($user->skills->isEmpty()) {
                fputcsv($out, ['Belum ada data keahlian']);
            } else {
                fputcsv($out, ['Keahlian', 'Level']);
                foreach ($user->skills as $skill) {
                    fputcsv($out, [$skill->skill_name, $skill->level ?? '—']);
                }
            }

            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
