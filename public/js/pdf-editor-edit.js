window.undo = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    let states = window.canvasStatesMap.get(canvas) || [];
    let redoStack = window.redoStackMap.get(canvas) || [];
    if (states.length < 2) return;
    redoStack.push(states.pop());
    window.canvasStatesMap.set(canvas, states);
    window.redoStackMap.set(canvas, redoStack);
    canvas.loadFromJSON(states[states.length - 1], () => {
        canvas.renderAll();
        // Save state after undo for correct redo
        window.saveCanvasState(canvas);
    });
};

window.redo = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    let states = window.canvasStatesMap.get(canvas) || [];
    let redoStack = window.redoStackMap.get(canvas) || [];
    if (!redoStack.length) return;
    const state = redoStack.pop();
    states.push(state);
    window.canvasStatesMap.set(canvas, states);
    window.redoStackMap.set(canvas, redoStack);
    canvas.loadFromJSON(state, () => {
        canvas.renderAll();
        // Save state after redo for correct undo
        window.saveCanvasState(canvas);
    });
};

let clipboardObj = null;

window.copyObject = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj) {
        obj.clone(function (cloned) {
            clipboardObj = cloned;
        });
    }
};

window.cutObject = function () {
    const obj = window.activeCanvas?.getActiveObject();
    if (obj) {
        obj.clone(function (cloned) {
            clipboardObj = cloned;
            window.activeCanvas.remove(obj);
        });
    }
};

window.pasteObject = function () {
    if (!window.activeCanvas || !clipboardObj) return;
    clipboardObj.clone(function (cloned) {
        cloned.set({ left: (cloned.left || 0) + 20, top: (cloned.top || 0) + 20 });
        window.activeCanvas.add(cloned);
        window.activeCanvas.setActiveObject(cloned);
        window.activeCanvas.renderAll();
    });
};

window.deleteSelected = function () {
    const canvas = window.getCanvas();
    const obj = canvas?.getActiveObject();
    if (obj) {
        canvas.remove(obj);
        window.saveCanvasState(canvas);
    }
};

window.duplicateObject = function () {
    const canvas = window.getCanvas();
    const obj = canvas?.getActiveObject();
    if (obj) {
        obj.clone(function (cloned) {
            cloned.set({ left: (obj.left || 0) + 30, top: (obj.top || 0) + 30 });
            canvas.add(cloned);
            canvas.setActiveObject(cloned);
            canvas.renderAll();
        });
    }
};

window.duplicateMultiple = function () {
    const canvas = window.getCanvas();
    const obj = canvas?.getActiveObject();
    if (window.showModal) {
        window.showModal({
            message: 'Duplicate Multiple',
            inputs: [{ id: 'count', label: 'How many duplicates?', type: 'number', value: 3 }],
            onOk: ({ count }) => {
                const n = parseInt(count);
                if (obj && !isNaN(n) && n > 0) {
                    for (let i = 1; i <= n; i++) {
                        obj.clone(function (cloned) {
                            cloned.set({
                                left: (obj.left || 0) + i * 20,
                                top: (obj.top || 0) + i * 20
                            });
                            canvas.add(cloned);
                        });
                    }
                    canvas.renderAll();
                    window.saveCanvasState(canvas);
                }
            }
        });
    }
};

window.findReplace = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Find and Replace Text',
            inputs: [
                { id: 'find', label: 'Find', type: 'text', value: '' },
                { id: 'replace', label: 'Replace', type: 'text', value: '' }
            ],
            onOk: ({ find, replace }) => {
                if (!find) return;
                canvas.getObjects('i-text').forEach(obj => {
                    if (obj.text && obj.text.includes(find)) {
                        obj.text = obj.text.split(find).join(replace);
                    }
                });
                canvas.renderAll();
            }
        });
    }
};

window.findReplaceAllPages = function () {
    if (!window.fabricCanvases || !window.showModal) return;
    window.showModal({
        message: 'Find and Replace Text (All Pages)',
        inputs: [
            { id: 'find', label: 'Find', type: 'text', value: '' },
            { id: 'replace', label: 'Replace', type: 'text', value: '' }
        ],
        onOk: ({ find, replace }) => {
            if (!find) return;
            window.fabricCanvases.forEach(canvas => {
                canvas.getObjects('i-text').forEach(obj => {
                    if (obj.text && obj.text.includes(find)) {
                        obj.text = obj.text.split(find).join(replace);
                    }
                });
                canvas.renderAll();
                window.saveCanvasState && window.saveCanvasState(canvas);
            });
        }
    });
};

window.clearAllObjects = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.confirm) {
        if (confirm('Remove all objects from this page?')) {
            canvas.getObjects().forEach(obj => canvas.remove(obj));
            canvas.renderAll();
        }
    }
};

window.selectAllObjects = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    canvas.discardActiveObject();
    const sel = new fabric.ActiveSelection(canvas.getObjects(), { canvas: canvas });
    canvas.setActiveObject(sel);
    canvas.requestRenderAll();
};

// Patch: Always save state after add, remove, modify, and text editing
if (window.setupCanvasEvents) {
    const origSetup = window.setupCanvasEvents;
    window.setupCanvasEvents = function (canvas) {
        origSetup(canvas);
        canvas.on('object:added', function (e) {
            window.saveCanvasState(canvas);
            // If added object is IText, add text change listeners
            if (e && e.target && e.target.type === 'i-text') {
                addITextListeners(e.target, canvas);
            }
        });
        canvas.on('object:modified', () => window.saveCanvasState(canvas));
        canvas.on('object:removed', () => window.saveCanvasState(canvas));
        // Add listeners to all existing IText objects
        canvas.getObjects('i-text').forEach(obj => addITextListeners(obj, canvas));
    };

    function addITextListeners(itext, canvas) {
        if (!itext._hasTextUndoListener) {
            itext.on('changed', () => window.saveCanvasState(canvas));
            itext.on('editing:exited', () => window.saveCanvasState(canvas));
            itext._hasTextUndoListener = true;
        }
    }
}
