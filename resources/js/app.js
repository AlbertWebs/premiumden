import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.querySelectorAll('[data-article-editor]').forEach((editor) => {
    const surface = editor.querySelector('.article-rich-surface');
    const source = editor.querySelector('.article-rich-source');
    if (!surface || !source) return;

    const sync = () => { source.value = surface.innerHTML; };
    editor.classList.add('is-enhanced');
    editor.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            surface.focus();
            document.execCommand(button.dataset.editorCommand, false, button.dataset.editorValue || null);
            sync();
        });
    });
    surface.addEventListener('input', sync);
    surface.addEventListener('paste', () => window.setTimeout(sync, 0));
    const form = editor.closest('form');
    if (form) form.addEventListener('submit', sync);
    sync();
});

document.querySelectorAll('[data-deal-dropzone]').forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    const fileList = zone.querySelector('[data-deal-file-list]');
    if (!input || !fileList) return;

    const renderFiles = (files) => {
        fileList.replaceChildren();
        [...files].forEach((file) => {
            const item = document.createElement('li');
            const name = document.createElement('span');
            const size = document.createElement('small');
            name.textContent = file.name;
            size.textContent = `${Math.max(1, Math.round(file.size / 1024))} KB`;
            item.append(name, size);
            fileList.append(item);
        });
        zone.classList.toggle('has-files', files.length > 0);
    };

    input.addEventListener('change', () => renderFiles(input.files));
    zone.addEventListener('dragover', (event) => {
        event.preventDefault();
        zone.classList.add('is-dragging');
    });
    zone.addEventListener('dragleave', (event) => {
        if (!zone.contains(event.relatedTarget)) zone.classList.remove('is-dragging');
    });
    zone.addEventListener('drop', (event) => {
        event.preventDefault();
        zone.classList.remove('is-dragging');
        const transfer = new DataTransfer();
        [...event.dataTransfer.files].forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
});

// Add scroll reveals only when the browser can run them. Without JavaScript,
// unsupported observers, or when reduced motion is requested, all content stays visible.
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reduceMotion && 'IntersectionObserver' in window) {
    const revealTargets = document.querySelectorAll([
        'main > *',
        '.article-grid > article',
        '.leadership-grid > article',
        '.package-grid > article',
        '.process-grid > article',
        '.application-journey > li',
    ].join(','));

    if (revealTargets.length) {
        revealTargets.forEach((element, index) => {
            element.dataset.reveal = '';
            element.style.setProperty('--reveal-delay', `${(index % 4) * 65}ms`);
        });
        document.documentElement.classList.add('motion-ready');

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.dataset.revealVisible = '';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -32px 0px' });

        revealTargets.forEach((element) => revealObserver.observe(element));
    }
}
