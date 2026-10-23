import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

document.addEventListener('change', (event) => {
    const input = event.target;

    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.dataset.previewTarget) {
        return;
    }

    const previewLink = document.getElementById(input.dataset.previewTarget);

    if (!(previewLink instanceof HTMLAnchorElement)) {
        return;
    }

    if (previewLink.dataset.previewObjectUrl) {
        URL.revokeObjectURL(previewLink.dataset.previewObjectUrl);
        delete previewLink.dataset.previewObjectUrl;
    }

    const selectedFile = input.files?.[0];
    const allowedTypes = (input.dataset.previewTypes ?? '').split(',');

    if (!selectedFile || !allowedTypes.includes(selectedFile.type)) {
        previewLink.href = previewLink.dataset.previewFallback ?? previewLink.href;

        return;
    }

    const objectUrl = URL.createObjectURL(selectedFile);
    previewLink.href = objectUrl;
    previewLink.dataset.previewObjectUrl = objectUrl;
});
