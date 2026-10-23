<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatusEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Seluruh jadwal wawancara & microteaching yang tercatat di sistem.
     */
    public function index(Request $request): View
    {
        $schedules = InterviewSchedule::with(['application.job.department', 'application.user'])
            ->orderByDesc('interview_date')
            ->paginate(25);

        $pending = Application::whereIn('status', [
            ApplicationStatusEnum::Passed,
            ApplicationStatusEnum::Interview,
        ])->count();

        return view('admin.schedules.index', compact('schedules', 'pending'));
    }

    /**
     * Buat atau perbarui jadwal wawancara untuk sebuah lamaran.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $interviewers = User::where('role', RoleEnum::Manajer)->get(['name', 'username'])
            ->map(fn (User $user): string => $user->username ?: $user->name)
            ->all();
        $isOtherInterviewer = $request->input('interviewer_name') === '__other__';

        $validated = $request->validate([
            'interview_date' => ['required', 'date', 'after_or_equal:today'],
            'interview_time' => ['required', 'date_format:H:i'],
            'interview_type' => ['required', 'in:online,offline'],
            'interview_location' => ['required', 'string', 'max:500'],
            'interviewer_name' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) use ($isOtherInterviewer, $interviewers): void {
                if (! $isOtherInterviewer && ! in_array($value, $interviewers, true)) {
                    $fail('Pilih pewawancara dari daftar manajer atau pilih nama lain.');
                }
            }],
            'interviewer_other' => [$isOtherInterviewer ? 'required' : 'nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'interview_date.after_or_equal' => 'Tanggal wawancara tidak boleh di masa lalu.',
            'interview_time.date_format' => 'Format jam tidak valid.',
            'interview_type.required' => 'Jenis wawancara wajib dipilih.',
            'interview_location.required' => 'Lokasi atau tautan wawancara wajib diisi.',
        ]);

        $validated['interviewer_name'] = $isOtherInterviewer
            ? trim($validated['interviewer_other'])
            : $validated['interviewer_name'];
        unset($validated['interviewer_other']);

        $schedule = $application->interviewSchedules()->latest('id')->first();
        if ($schedule) {
            $schedule->update($validated);
        } else {
            $application->interviewSchedules()->create($validated);
        }

        if ($application->status !== ApplicationStatusEnum::Hired) {
            $application->update(['status' => ApplicationStatusEnum::Interview]);
        }

        return redirect()->back()
            ->with('success', 'Jadwal wawancara tersimpan dan pelamar telah diberi status Interview.');
    }

    /**
     * Batalkan jadwal wawancara.
     */
    public function destroy(InterviewSchedule $schedule): RedirectResponse
    {
        $application = $schedule->application;

        $schedule->delete();

        if ($application && $application->status === ApplicationStatusEnum::Interview) {
            $application->update(['status' => ApplicationStatusEnum::Passed]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal wawancara dibatalkan.');
    }
}
