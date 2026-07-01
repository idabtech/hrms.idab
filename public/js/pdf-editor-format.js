// Format tab actions: toggleBold, toggleItalic, toggleUnderline, toggleStrikethrough, toggleSuperscript, toggleSubscript, highlightText, insertDateTime, insertHyperlink, updateFontSize, alignText, bringToFront, sendToBack, updateFontColor, updateTextBgColor, updateFontFamily

window.toggleBold = function () {
    const obj = window.getActiveIText && window.getActiveIText();
    if (obj) {
        obj.set("fontWeight", obj.fontWeight === 'bold' ? 'normal' : 'bold');
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.toggleItalic = function () {
    const obj = window.getActiveIText && window.getActiveIText();
    if (obj) {
        obj.set("fontStyle", obj.fontStyle === 'italic' ? 'normal' : 'italic');
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.toggleUnderline = function () {
    const obj = window.getActiveIText && window.getActiveIText();
    if (obj) {
        obj.set("underline", !obj.underline);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.toggleStrikethrough = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj && obj.type === 'i-text') {
        obj.set('linethrough', !obj.linethrough);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.toggleSuperscript = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj && obj.type === 'i-text') {
        obj.set('fontSize', obj.fontSize * 0.7);
        obj.set('top', (obj.top || 0) - 10);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.toggleSubscript = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj && obj.type === 'i-text') {
        obj.set('fontSize', obj.fontSize * 0.7);
        obj.set('top', (obj.top || 0) + 10);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.highlightText = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj && obj.type === 'i-text') {
        obj.set('backgroundColor', '#fff59d');
        window.activeCanvas.renderAll();
    }
};

window.insertDateTime = function () {
    if (!window.activeCanvas) return;
    const now = new Date();
    const text = new fabric.IText(now.toLocaleString(), {
        left: 120, top: 120, fontSize: 16, fill: '#333'
    });
    window.activeCanvas.add(text);
    window.activeCanvas.setActiveObject(text);
    window.saveCanvasState(window.activeCanvas);
};

window.insertHyperlink = function () {
    if (!window.activeCanvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter URL:',
            inputs: [{ id: 'url', label: 'URL', type: 'text', value: 'https://' }],
            onOk: ({ url }) => {
                if (!url) return;
                const text = new fabric.IText(url, {
                    left: 140, top: 140, fontSize: 16, fill: '#1976d2', underline: true
                });
                text.on('mousedown', function () {
                    window.open(url, '_blank');
                });
                window.activeCanvas.add(text);
                window.activeCanvas.setActiveObject(text);
                window.saveCanvasState(window.activeCanvas);
            }
        });
    }
};

window.updateFontSize = function () {
    const canvas = window.getCanvas();
    const obj = canvas?.getActiveObject();
    if (obj && document.getElementById('font-size')) {
        obj.set({ fontSize: parseInt(document.getElementById('font-size').value) });
        canvas.renderAll();
        window.saveCanvasState(canvas);
    }
};

window.alignText = function (align) {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj && (obj.type === 'i-text' || obj.type === 'textbox')) {
        obj.set('textAlign', align);
        window.activeCanvas.renderAll();
    }
};

window.bringToFront = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj) {
        window.activeCanvas.bringToFront(obj);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.sendToBack = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj) {
        window.activeCanvas.sendToBack(obj);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.updateFontColor = function () {
    const canvas = window.getCanvas();
    const obj = canvas?.getActiveObject();
    if (obj && document.getElementById('font-color')) {
        obj.set({ fill: document.getElementById('font-color').value });
        canvas.renderAll();
        window.saveCanvasState(canvas);
    }
};

window.updateTextBgColor = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj && obj.type === 'i-text' && document.getElementById('text-bg-color')) {
        obj.set('backgroundColor', document.getElementById('text-bg-color').value);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};

window.updateFontFamily = function () {
    const obj = window.getActiveIText && window.getActiveIText();
    const selectedFont = document.getElementById("font-family") ? document.getElementById("font-family").value : null;
    if (obj && selectedFont) {
        obj.set("fontFamily", selectedFont);
        window.activeCanvas.renderAll();
        window.saveCanvasState(window.activeCanvas);
    }
};
