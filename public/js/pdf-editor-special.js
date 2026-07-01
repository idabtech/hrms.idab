// Special tab actions: addStamp, addSignature, addStickyNote, addCircleStamp

window.addStamp = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const text = new fabric.IText("APPROVED", {
        left: 200, top: 200, fontSize: 32, fill: "#388e3c", fontWeight: "bold", angle: -20, opacity: 0.7
    });
    canvas.add(text);
    canvas.setActiveObject(text);
};

window.addSignature = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    if (window.showModal) {
        window.showModal({
            message: 'Enter signature text:',
            inputs: [{ id: 'sig', label: 'Signature', type: 'text', value: '' }],
            onOk: ({ sig }) => {
                if (!sig) return;
                const text = new fabric.IText(sig, {
                    left: 220, top: 250, fontSize: 28, fill: "#222", fontFamily: "Pacifico", fontStyle: "italic"
                });
                canvas.add(text);
                canvas.setActiveObject(text);
            }
        });
    }
};

window.addStickyNote = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const note = new fabric.IText("Note...", {
        left: 250, top: 250, fontSize: 18, fill: "#333", backgroundColor: "#fffde7", width: 120, height: 60, padding: 8
    });
    canvas.add(note);
    canvas.setActiveObject(note);
};

window.addCircleStamp = function () {
    const canvas = window.getCanvas();
    if (!canvas) return;
    const circle = new fabric.Circle({
        left: 250, top: 250, radius: 40, fill: 'rgba(0,200,0,0.1)', stroke: '#388e3c', strokeWidth: 3
    });
    const text = new fabric.IText('STAMP', {
        left: 250, top: 250, fontSize: 18, fill: '#388e3c', fontWeight: 'bold', originX: 'center', originY: 'center'
    });
    const group = new fabric.Group([circle, text], { left: 250, top: 250 });
    canvas.add(group);
    canvas.setActiveObject(group);
};
