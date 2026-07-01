window.exportCanvasAsImage = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const dataURL = canvas.toDataURL({ format: 'png' });
    const link = document.createElement('a');
    link.download = 'edited_canvas.png';
    link.href = dataURL;
    link.click();
};

window.resetCanvas = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Reset this page to blank?',
            okText: 'Reset',
            cancelText: 'Cancel',
            onOk: () => {
                canvas.clear();
                window.saveCanvasState(canvas);
            }
        });
    } else {
        if (confirm('Reset this page to blank?')) {
            canvas.clear();
            window.saveCanvasState(canvas);
        }
    }
};

window.downloadPDF = function () {
    window.open(window.PDF_EDITOR_DATA.pdfUrl, '_blank');
};

window.printPDF = function () {
    const win = window.open(window.PDF_EDITOR_DATA.pdfUrl, '_blank');
    if (win) {
        win.onload = function () {
            win.print();
        };
    }
};

window.addPage = function () {
    // Ensure fabricCanvases is initialized
    if (!window.fabricCanvases) window.fabricCanvases = [];
    const fabricCanvases = window.fabricCanvases;
    // If no canvases exist, show error
    if (fabricCanvases.length === 0) {
        if (window.showInfo) window.showInfo('No pages to add before/after. Please reload the PDF.');
        return;
    }
    // Ensure activeCanvas is set
    if (!window.activeCanvas) window.activeCanvas = fabricCanvases[0];

    if (window.showModal) {
        window.showModal({
            message: 'Add page before or after which page?',
            inputs: [
                { id: 'page', label: 'Page Number', type: 'number', value: fabricCanvases.indexOf(window.activeCanvas) + 1 },
                { id: 'where', label: 'Position', type: 'text', value: 'after' }
            ],
            okText: 'Add Page',
            onOk: ({ page, where }) => {
                let idx = parseInt(page) - 1;
                if (isNaN(idx) || idx < 0 || idx > fabricCanvases.length - 1) idx = fabricCanvases.length - 1;
                let width = fabricCanvases[idx].width;
                let height = fabricCanvases[idx].height;
                const wrapper = document.createElement('div');
                wrapper.className = 'border relative p-1';
                const fabricCanvasEl = document.createElement('canvas');
                fabricCanvasEl.width = width;
                fabricCanvasEl.height = height;
                wrapper.appendChild(fabricCanvasEl);
                const container = document.getElementById('pdf-editor-container');
                // Defensive: ensure container exists
                if (!container) {
                    alert('PDF editor container not found.');
                    return;
                }
                // Insert wrapper at correct position
                let insertIdx = (where === 'before') ? idx : idx + 1;
                if (insertIdx > container.children.length) insertIdx = container.children.length;
                if (insertIdx < 0) insertIdx = 0;
                if (insertIdx >= container.children.length) {
                    container.appendChild(wrapper);
                } else {
                    container.insertBefore(wrapper, container.children[insertIdx]);
                }
                // Create new fabric.Canvas
                const fabricCanvas = new fabric.Canvas(fabricCanvasEl, {
                    selection: true,
                    preserveObjectStacking: true
                });
                if (typeof window.setupCanvasEvents === 'function') {
                    window.setupCanvasEvents(fabricCanvas);
                }
                // Insert into fabricCanvases array
                fabricCanvases.splice(insertIdx, 0, fabricCanvas);
                window.activeCanvas = fabricCanvas;
                // Notify tools.js
                document.dispatchEvent(new CustomEvent('activeCanvasChanged', { detail: { canvas: fabricCanvas } }));
                // Scroll into view
                wrapper.scrollIntoView({ behavior: 'smooth' });
                // Optionally, save state
                if (typeof window.saveCanvasState === 'function') {
                    window.saveCanvasState(fabricCanvas);
                }
            }
        });
    }
};

window.deletePage = function () {
    const fabricCanvases = window.fabricCanvases;
    if (fabricCanvases.length <= 1) {
        if (window.showInfo) window.showInfo('At least one page must remain.');
        return;
    }
    if (window.showModal) {
        window.showModal({
            message: 'Delete which page?',
            inputs: [
                { id: 'page', label: 'Page Number', type: 'number', value: fabricCanvases.indexOf(window.activeCanvas) + 1 }
            ],
            okText: 'Delete Page',
            onOk: ({ page }) => {
                let idx = parseInt(page) - 1;
                if (isNaN(idx) || idx < 0 || idx > fabricCanvases.length - 1) idx = fabricCanvases.indexOf(window.activeCanvas);
                // Remove the complete wrapper div for the page
                const canvas = fabricCanvases[idx];
                const wrapper = canvas.getElement().parentElement.parentElement;
                if (wrapper && wrapper.parentElement) {
                    wrapper.parentElement.removeChild(wrapper);
                }
                fabricCanvases.splice(idx, 1);
                window.activeCanvas = fabricCanvases[Math.max(0, idx - 1)];
                document.dispatchEvent(new CustomEvent('activeCanvasChanged', { detail: { canvas: window.activeCanvas } }));
            }
        });
    }
};

window.duplicatePage = function () {
    const fabricCanvases = window.fabricCanvases;
    const activeCanvas = window.activeCanvas;
    if (!activeCanvas) return;
    const idx = fabricCanvases.indexOf(activeCanvas);
    const json = activeCanvas.toDatalessJSON();
    let width = activeCanvas.width, height = activeCanvas.height;
    const wrapper = document.createElement('div');
    wrapper.className = 'border relative p-1';
    const fabricCanvasEl = document.createElement('canvas');
    fabricCanvasEl.width = width;
    fabricCanvasEl.height = height;
    wrapper.appendChild(fabricCanvasEl);
    const container = document.getElementById('pdf-editor-container');
    container.insertBefore(wrapper, container.children[idx + 1]);
    const fabricCanvas = new fabric.Canvas(fabricCanvasEl, {
        selection: true,
        preserveObjectStacking: true
    });
    fabricCanvas.loadFromJSON(json, () => fabricCanvas.renderAll());
    window.setupCanvasEvents(fabricCanvas);
    fabricCanvases.splice(idx + 1, 0, fabricCanvas);
    window.activeCanvas = fabricCanvas;
    document.dispatchEvent(new CustomEvent('activeCanvasChanged', { detail: { canvas: fabricCanvas } }));
    wrapper.scrollIntoView({ behavior: 'smooth' });
};

// Move page up
window.movePageUp = function () {
    if (!activeCanvas) return;
    const idx = fabricCanvases.indexOf(activeCanvas);
    if (idx <= 0) return;
    [fabricCanvases[idx - 1], fabricCanvases[idx]] = [fabricCanvases[idx], fabricCanvases[idx - 1]];
    const container = document.getElementById('pdf-editor-container');
    container.insertBefore(container.children[idx], container.children[idx - 1]);
};

// Move page down
window.movePageDown = function () {
    if (!activeCanvas) return;
    const idx = fabricCanvases.indexOf(activeCanvas);
    if (idx === -1 || idx >= fabricCanvases.length - 1) return;
    [fabricCanvases[idx], fabricCanvases[idx + 1]] = [fabricCanvases[idx + 1], fabricCanvases[idx]];
    const container = document.getElementById('pdf-editor-container');
    container.insertBefore(container.children[idx + 1], container.children[idx]);
};

window.jumpToPage = function () {
    const fabricCanvases = window.fabricCanvases;
    if (fabricCanvases.length < 2) return;
    if (window.showModal) {
        window.showModal({
            message: 'Go to which page?',
            inputs: [
                { id: 'page', label: 'Page Number', type: 'number', value: fabricCanvases.indexOf(window.activeCanvas) + 1 }
            ],
            okText: 'Go',
            onOk: ({ page }) => {
                let idx = parseInt(page) - 1;
                if (isNaN(idx) || idx < 0 || idx > fabricCanvases.length - 1) return;
                window.activeCanvas = fabricCanvases[idx];
                const wrapper = window.activeCanvas.getElement().parentElement;
                if (wrapper) wrapper.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
};

window.addPageHeader = function () {
    if (!window.fabricCanvases || window.fabricCanvases.length === 0) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter header text:',
            inputs: [{ id: 'header', label: 'Header', type: 'text', value: 'Header' }],
            onOk: ({ header }) => {
                if (!header) return;
                window.fabricCanvases.forEach(canvas => {
                    const text = new fabric.IText(header, {
                        left: 40,
                        top: 20,
                        fontSize: 20,
                        fill: '#1976d2',
                        fontWeight: 'bold'
                    });
                    canvas.add(text);
                    canvas.setActiveObject(text);
                });
            }
        });
    }
};

window.addPageTitle = function () {
    if (!window.fabricCanvases || window.fabricCanvases.length === 0) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter page title:',
            inputs: [{ id: 'title', label: 'Title', type: 'text', value: 'Page Title' }],
            onOk: ({ title }) => {
                if (!title) return;
                window.fabricCanvases.forEach(canvas => {
                    const text = new fabric.IText(title, {
                        left: canvas.width / 2,
                        top: 40,
                        fontSize: 28,
                        fill: '#333',
                        fontWeight: 'bold',
                        originX: 'center'
                    });
                    canvas.add(text);
                    canvas.setActiveObject(text);
                });
            }
        });
    }
};

window.addWatermark = function () {
    if (!window.fabricCanvases || window.fabricCanvases.length === 0) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter watermark text:',
            inputs: [{ id: 'wm', label: 'Watermark', type: 'text', value: 'CONFIDENTIAL' }],
            onOk: ({ wm }) => {
                if (!wm) return;
                window.fabricCanvases.forEach(canvas => {
                    const text = new fabric.IText(wm, {
                        left: canvas.width / 2,
                        top: canvas.height / 2,
                        fontSize: 48,
                        fill: '#1976d2',
                        opacity: 0.12,
                        fontWeight: 'bold',
                        angle: -30,
                        originX: 'center',
                        originY: 'center'
                    });
                    canvas.add(text);
                    canvas.setActiveObject(text);
                });
            }
        });
    }
};

window.addPageFooter = function () {
    if (!window.fabricCanvases || window.fabricCanvases.length === 0) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter footer text:',
            inputs: [{ id: 'footer', label: 'Footer', type: 'text', value: 'Footer' }],
            onOk: ({ footer }) => {
                if (!footer) return;
                window.fabricCanvases.forEach(canvas => {
                    const text = new fabric.IText(footer, {
                        left: 40,
                        top: canvas.height - 30,
                        fontSize: 16,
                        fill: '#888'
                    });
                    canvas.add(text);
                    canvas.setActiveObject(text);
                });
            }
        });
    }
};

window.setPageBackgroundImage = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter background image URL:',
            inputs: [{ id: 'url', label: 'Image URL', type: 'text' }],
            onOk: ({ url }) => {
                if (!url) return;
                fabric.Image.fromURL(url, img => {
                    canvas.setBackgroundImage(null, null);
                    const scaleX = canvas.width / img.width;
                    const scaleY = canvas.height / img.height;
                    img.set({
                        originX: 'left',
                        originY: 'top',
                        left: 0,
                        top: 0,
                        scaleX: scaleX,
                        scaleY: scaleY,
                        selectable: false,
                        evented: false
                    });
                    canvas.setBackgroundImage(img, () => {
                        canvas.renderAll();
                        window.saveCanvasState(canvas);
                    });
                }, { crossOrigin: 'anonymous' });
            }
        });
    }
};

window.setPageBackgroundFromFile = function (event) {
    if (!event || !event.target || !event.target.files) {
        if (!document.getElementById('pdf-bg-upload')) {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.style.display = 'none';
            input.id = 'pdf-bg-upload';
            document.body.appendChild(input);
            input.addEventListener('change', function (event) {
                window.setPageBackgroundFromFile(event);
                input.value = '';
            });
        }
        document.getElementById('pdf-bg-upload').click();
        return;
    }

    const canvas = window.getCanvas();
    if (!canvas) return;

    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = evt => {
        fabric.Image.fromURL(evt.target.result, img => {
            canvas.setBackgroundImage(null, null); // Clear old BG

            const scaleX = canvas.width / img.width;
            const scaleY = canvas.height / img.height;

            img.set({
                originX: 'left',
                originY: 'top',
                left: 0,
                top: 0,
                scaleX,
                scaleY,
                selectable: false,
                evented: false
            });

            canvas.setBackgroundImage(img, () => {
                canvas.renderAll();
                window.saveCanvasState && window.saveCanvasState(canvas);
            });
        });
    };
    reader.readAsDataURL(file);
};


// Fallback for getCanvas if not defined
if (typeof window.getCanvas !== 'function') {
    window.getCanvas = function () {
        if (window.activeCanvas) return window.activeCanvas;
        if (window.fabricCanvases && window.fabricCanvases.length > 0) return window.fabricCanvases[0];
        return null;
    };
}

// Modal implementation using #global-modal
window.showModal = function (opts) {
    const modal = document.getElementById('global-modal');
    const content = document.getElementById('global-modal-content');
    const message = document.getElementById('global-modal-message');
    const noteMessage = document.getElementById('global-modal-note');
    const form = document.getElementById('global-modal-form');
    const okBtn = document.getElementById('global-modal-ok');
    const cancelBtn = document.getElementById('global-modal-cancel');
    if (!modal || !content || !message || !form || !okBtn || !cancelBtn) {
        alert(opts.message || 'Modal not available.');
        return;
    }
    // Reset
    form.innerHTML = '';
    message.textContent = opts.message || '';
    noteMessage.textContent = opts.noteText || '';
    okBtn.textContent = opts.okText || 'OK';
    cancelBtn.textContent = opts.cancelText || 'Cancel';
    // Build inputs
    let values = {};
    if (Array.isArray(opts.inputs)) {
        opts.inputs.forEach(input => {
            const wrapper = document.createElement('div');
            wrapper.style.marginBottom = '0.7em';
            const label = document.createElement('label');
            label.textContent = input.label || input.id;
            label.style.display = 'block';
            label.style.fontWeight = '500';
            label.style.marginBottom = '0.2em';
            wrapper.appendChild(label);
            let el;
            if (input.type === 'text' || input.type === 'number') {
                el = document.createElement('input');
                el.type = input.type;
                el.value = input.value || '';
                el.id = 'modal-input-' + input.id;
                el.style.width = '100%';
                el.style.padding = '0.3em';
                el.style.border = '1px solid #ddd';
                el.style.borderRadius = '4px';
                el.style.fontSize = '1em';
                el.required = true;
            }
            // Add more input types as needed
            if (el) {
                wrapper.appendChild(el);
                form.appendChild(wrapper);
                values[input.id] = el.value;
                el.oninput = () => { values[input.id] = el.value; };
            }
        });
    }
    // Show modal
    modal.style.display = 'flex';
    // Focus first input if any
    setTimeout(() => {
        const firstInput = form.querySelector('input');
        if (firstInput) firstInput.focus();
    }, 100);
    // Cancel handler
    function closeModal() {
        modal.style.display = 'none';
        form.onsubmit = null;
        cancelBtn.onclick = null;
        modal.onkeydown = null;
    }
    cancelBtn.onclick = function (e) {
        if (e) e.preventDefault();
        closeModal();
        if (typeof opts.onCancel === 'function') opts.onCancel();
    };
    // OK handler
    form.onsubmit = function (e) {
        if (e) e.preventDefault();
        closeModal();
        if (typeof opts.onOk === 'function') {
            // Collect values
            const result = {};
            if (Array.isArray(opts.inputs)) {
                opts.inputs.forEach(input => {
                    const el = form.querySelector('#modal-input-' + input.id);
                    result[input.id] = el ? el.value : '';
                });
            }
            opts.onOk(result);
        }
    };
    okBtn.onclick = function (e) {
        if (e) e.preventDefault();
        form.requestSubmit();
    };
    // Allow Esc to close and Enter to submit
    modal.onkeydown = function (e) {
        if (e.key === 'Escape') {
            closeModal();
            if (typeof opts.onCancel === 'function') opts.onCancel();
        }
        // Submit on Enter if an input is focused
        if (e.key === 'Enter') {
            const active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) {
                e.preventDefault();
                form.requestSubmit();
            }
        }
    };
    // Trap focus inside modal
    modal.tabIndex = -1;
    modal.focus();
};

// Fallback for showInfo if not defined
if (typeof window.showInfo !== 'function') {
    window.showInfo = function (msg) {
        alert(msg);
    };
}
