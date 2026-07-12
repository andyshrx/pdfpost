import { basicSetup, EditorView } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { oneDark } from '@codemirror/theme-one-dark';

// Mounts CodeMirror on the template editor page. Livewire must not touch the
// editor DOM (wire:ignore on the container), changes flow back through the
// onChange callback, debounced so the preview does not rerender per keystroke.
window.mountLiquidEditor = function (parent, doc, onChange) {
    let timer = null;

    const isDark = document.documentElement.classList.contains('dark');

    return new EditorView({
        parent,
        doc,
        extensions: [
            basicSetup,
            html(),
            ...(isDark ? [oneDark] : []),
            EditorView.updateListener.of((update) => {
                if (!update.docChanged) return;

                clearTimeout(timer);
                timer = setTimeout(() => onChange(update.state.doc.toString()), 600);
            }),
        ],
    });
};
