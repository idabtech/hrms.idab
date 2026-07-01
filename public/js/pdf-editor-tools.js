function showMenuTab(tabId) {
    document.querySelectorAll('.menu-panel').forEach(tab => tab.style.display = 'none');
    var panel = document.getElementById(tabId);
    if (panel) panel.style.display = 'block';
    document.querySelectorAll('.menu-tab-btn').forEach(btn => btn.classList.remove('active', 'bg-white'));
    const ids = ['file-menu', 'edit-menu', 'insert-menu', 'format-menu', 'shapes-menu', 'object-menu', 'image-menu',
        'special-menu', 'view-menu'
    ];
    var idx = ids.indexOf(tabId);
    if (idx !== -1) {
        document.querySelectorAll('.menu-tab-btn')[idx].classList.add('active', 'bg-white');
    }
    // Show first sub-tab by default
    var subTabs = panel ? panel.querySelectorAll('.sub-tab-btn') : [];
    if (subTabs.length) {
        subTabs.forEach(btn => btn.classList.remove('active'));
        subTabs[0].classList.add('active');
        var subPanels = panel.querySelectorAll('.sub-panel');
        subPanels.forEach((sp, i) => sp.style.display = i === 0 ? 'grid' : 'none');
    }
}
// Sub-tab logic
function showSubTab(subTabId) {
    // Find the parent menu-panel
    var subPanel = document.getElementById(subTabId);
    if (!subPanel) return;
    var parent = subPanel.closest('.menu-panel');
    if (!parent) return;
    // Hide all sub-panels
    parent.querySelectorAll('.sub-panel').forEach(sp => sp.style.display = 'none');
    // Show selected sub-panel
    subPanel.style.display = 'grid';
    // Set active sub-tab
    parent.querySelectorAll('.sub-tab-btn').forEach(btn => btn.classList.remove('active'));
    var btns = Array.from(parent.querySelectorAll('.sub-tab-btn'));
    var idx = btns.findIndex(btn => btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(subTabId));
    if (idx !== -1) btns[idx].classList.add('active');
}
document.addEventListener('DOMContentLoaded', function () {
    showMenuTab('file-menu');
});

// Toolbox collapse/expand logic (partial collapse: show move handle + 1 tab)
document.addEventListener('DOMContentLoaded', function () {
    var toolboxBtn = document.getElementById('toolbox-toggle-btn');
    var toolboxMain = document.getElementById('toolbox-main');
    var toolboxIcon = document.getElementById('toolbox-toggle-icon');
    var nav = toolboxMain.querySelector('nav');
    if (toolboxBtn && toolboxMain && toolboxIcon) {
        toolboxBtn.onclick = function () {
            var isHidden = toolboxMain.classList.toggle('toolbox-collapsed');
            // Hide all except move handle and nav (tabs)
            var children = Array.from(toolboxMain.children).filter(function (el) {
                return el.id !== 'toolbox-move-handle' && el.tagName !== 'NAV';
            });
            children.forEach(function (el) {
                el.style.display = isHidden ? 'none' : '';
            });
            // Always show nav (tab bar), but shrink it if hidden
            if (nav) {
                nav.style.display = '';
                nav.classList.toggle('toolbox-nav-collapsed', isHidden);
            }
            // If hidden, only show the first tab button (File)
            if (nav) {
                var tabBtns = nav.querySelectorAll('.menu-tab-btn');
                tabBtns.forEach(function (btn, idx) {
                    btn.style.display = isHidden && idx > 0 ? 'none' : '';
                });
            }
            toolboxIcon.textContent = isHidden ? '+' : '−';
            toolboxBtn.title = isHidden ? 'Expand Toolbox' : 'Collapse Toolbox';
        };
    }

    // Hide/Show (X/cross) logic
    var toolboxHideBtn = document.getElementById('toolbox-hide-btn');
    var toolboxShowBtn = document.getElementById('toolbox-show-btn');
    if (toolboxHideBtn && toolboxMain && toolboxShowBtn) {
        toolboxHideBtn.onclick = function () {
            toolboxMain.style.display = 'none';
            toolboxShowBtn.style.display = '';
        };
        toolboxShowBtn.onclick = function () {
            toolboxMain.style.display = '';
            toolboxShowBtn.style.display = 'none';
        };
    }
});

// Prevent toolbox from moving completely out of viewport on left or right side
(function limitToolbarDragSides() {
    // Change selector to match the new class (no .sticky)
    const toolbar = document.querySelector('div[style*="position: fixed"]');
    if (!toolbar) return;
    let offsetX = 0,
        dragging = false;
    toolbar.addEventListener('mousedown', function (e) {
        if (e.target.closest('.menu-btn-row,.menu-panel,.menu-tab-btn')) return;
        dragging = true;
        offsetX = e.clientX - toolbar.getBoundingClientRect().left;
        document.body.style.userSelect = 'none';
    });
    document.addEventListener('mousemove', function (e) {
        if (!dragging) return;
        // Always keep at least 0px on left, at least 60px visible on right
        const minVisible = 60;
        const winW = window.innerWidth;
        const tbW = toolbar.offsetWidth;
        let left = e.clientX - offsetX;
        if (left < 0) left = 0;
        if (left + minVisible > winW) left = winW - minVisible;
        toolbar.style.left = left + 'px';
        toolbar.style.top = '0px'; // Always fixed at top
        toolbar.style.right = 'auto';
    });
    document.addEventListener('mouseup', function () {
        dragging = false;
        document.body.style.userSelect = '';
    });
    // On window resize, ensure at least minVisible is visible and not off left
    window.addEventListener('resize', function () {
        const minVisible = 60;
        const winW = window.innerWidth;
        const tbW = toolbar.offsetWidth;
        let left = parseInt(toolbar.style.left, 10) || 0;
        if (left < 0) left = 0;
        if (left + minVisible > winW) left = winW - minVisible;
        toolbar.style.left = left + 'px';
        toolbar.style.top = '0px'; // Always fixed at top
    });
})();

document.addEventListener("DOMContentLoaded", () => {
    // --- Shared variables and setup ---
    const { pdfUrl, saveUrl, csrf } = window.PDF_EDITOR_DATA;
    const container = document.getElementById('pdf-editor-container');
    const fabricCanvases = [];
    let activeCanvas = null;

    // --- Undo/Redo State Management (per-canvas) ---
    const canvasStatesMap = new WeakMap();
    const redoStackMap = new WeakMap();

    function saveCanvasState(canvas) {
        if (!canvas) return;
        let states = canvasStatesMap.get(canvas) || [];
        let redoStack = redoStackMap.get(canvas) || [];
        const state = JSON.stringify(canvas.toDatalessJSON());
        // Only push if different from last state
        if (states.length === 0 || states[states.length - 1] !== state) {
            states.push(state);
            // Limit history to 50
            if (states.length > 50) states = states.slice(states.length - 50);
            canvasStatesMap.set(canvas, states);
            redoStackMap.set(canvas, []); // clear redo on new action
        }
    }
    function setupCanvasEvents(canvas) {
        canvas.on('object:added', () => saveCanvasState(canvas));
        canvas.on('object:modified', () => saveCanvasState(canvas));
        canvas.on('object:removed', () => saveCanvasState(canvas));
        saveCanvasState(canvas);
    }
    function getActiveIText() {
        const obj = activeCanvas?.getActiveObject();
        return obj && obj.type === 'i-text' ? obj : null;
    }

    // --- Fonts ---
    const fonts = ["Roboto", "Open Sans", "Lobster", "Pacifico", "Oswald", "Poppins"];
    (function loadGoogleFonts() {
        const link = document.createElement("link");
        link.href = `https://fonts.googleapis.com/css2?${fonts.map(f => `family=${f.replace(/ /g, '+')}`).join('&')}&display=swap`;
        link.rel = "stylesheet";
        document.head.appendChild(link);
    })();
    (function populateFontSelector() {
        const fontSelect = document.getElementById("font-family");
        if (!fontSelect) return;
        fonts.forEach(f => {
            const opt = document.createElement("option");
            opt.value = f;
            opt.innerText = f;
            fontSelect.appendChild(opt);
        });
    })();

    // --- PDF rendering and Fabric canvas setup ---
    pdfjsLib.getDocument(pdfUrl).promise.then(async pdf => {
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            const page = await pdf.getPage(pageNum);
            const scale = 1.5;
            const viewport = page.getViewport({ scale });

            const wrapper = document.createElement('div');
            wrapper.className = 'border relative p-1';

            const fabricCanvasEl = document.createElement('canvas');
            fabricCanvasEl.width = viewport.width;
            fabricCanvasEl.height = viewport.height;
            wrapper.appendChild(fabricCanvasEl);

            container.appendChild(wrapper);

            const fabricCanvas = new fabric.Canvas(fabricCanvasEl, {
                selection: true,
                preserveObjectStacking: true
            });

            // Add PDF text as editable objects
            const textContent = await page.getTextContent();
            textContent.items.forEach(item => {
                const tx = pdfjsLib.Util.transform(viewport.transform, item.transform);
                fabricCanvas.add(new fabric.IText(item.str, {
                    left: tx[4],
                    top: tx[5] - item.height * scale,
                    fontSize: item.height * scale,
                    fill: '#000',
                    selectable: true
                }));
            });

            // Generic drag & drop (image, text, files)
            fabricCanvasEl.addEventListener('dragover', e => {
                e.preventDefault();
            });
            fabricCanvasEl.addEventListener('drop', e => {
                e.preventDefault();
                // Handle files (images, text, etc.)
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    const file = e.dataTransfer.files[0];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = evt => {
                            fabric.Image.fromURL(evt.target.result, img => {
                                img.set({ left: 100, top: 100, scaleX: 0.5, scaleY: 0.5 });
                                fabricCanvas.add(img);
                            });
                        };
                        reader.readAsDataURL(file);
                    } else if (file.type.startsWith('text/')) {
                        const reader = new FileReader();
                        reader.onload = evt => {
                            const text = new fabric.IText(evt.target.result, {
                                left: 100,
                                top: 100,
                                fontSize: 20,
                                fill: '#333'
                            });
                            fabricCanvas.add(text);
                        };
                        reader.readAsText(file);
                    }
                    // You can add more file type handlers here if needed
                } else if (e.dataTransfer.items && e.dataTransfer.items.length > 0) {
                    // Handle dragged text or URLs
                    for (let i = 0; i < e.dataTransfer.items.length; i++) {
                        const item = e.dataTransfer.items[i];
                        if (item.kind === 'string' && item.type === 'text/plain') {
                            item.getAsString(str => {
                                const text = new fabric.IText(str, {
                                    left: 100,
                                    top: 100,
                                    fontSize: 20,
                                    fill: '#333'
                                });
                                fabricCanvas.add(text);
                            });
                        } else if (item.kind === 'string' && item.type === 'text/uri-list') {
                            item.getAsString(url => {
                                // Try to add as image if it's an image URL
                                if (/\.(jpg|jpeg|png|gif|webp|svg)$/i.test(url)) {
                                    fabric.Image.fromURL(url, img => {
                                        img.set({ left: 100, top: 100, scaleX: 0.5, scaleY: 0.5 });
                                        fabricCanvas.add(img);
                                    }, { crossOrigin: 'anonymous' });
                                } else {
                                    // Otherwise, add as text
                                    const text = new fabric.IText(url, {
                                        left: 100,
                                        top: 100,
                                        fontSize: 20,
                                        fill: '#333'
                                    });
                                    fabricCanvas.add(text);
                                }
                            });
                        }
                    }
                }
            });

            // Always set the first canvas as activeCanvas after all pages loaded
            if (pageNum === 1) {
                activeCanvas = fabricCanvas;
            }

            // Set activeCanvas on mouse:down
            fabricCanvas.on('mouse:down', () => {
                activeCanvas = fabricCanvas;
                window.activeCanvas = fabricCanvas;
            });

            setupCanvasEvents(fabricCanvas);
            fabricCanvases.push(fabricCanvas);
        }
        // After all canvases are created, set activeCanvas to the first one if not set
        if (!activeCanvas && fabricCanvases.length > 0) {
            activeCanvas = fabricCanvases[0];
        }
    });

    // --- Utility: getCanvas() ---
    function getCanvas() {
        if (activeCanvas) return activeCanvas;
        if (fabricCanvases.length > 0) {
            activeCanvas = fabricCanvases[0];
            return activeCanvas;
        }
        return null;
    }

    document.addEventListener('keydown', function (e) {
        const canvas = getCanvas();
        if (!canvas || document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') return;

        // Ctrl/Cmd + Z (Undo)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
            e.preventDefault();
            window.undo();
        }
        // Ctrl/Cmd + Y (Redo)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
            e.preventDefault();
            window.redo();
        }
        // Delete or Backspace (Delete selected)
        if ((e.key === 'Delete' || e.key === 'Backspace') && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            window.deleteSelected();
        }
        // Ctrl/Cmd + C (Copy)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c') {
            e.preventDefault();
            window.copyObject();
        }
        // Ctrl/Cmd + X (Cut)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'x') {
            e.preventDefault();
            window.cutObject();
        }
        // Ctrl/Cmd + V (Paste)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v') {
            e.preventDefault();
            window.pasteObject();
        }
        // Ctrl/Cmd + B (Bold)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            window.toggleBold();
        }
        // Ctrl/Cmd + I (Italic)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'i') {
            e.preventDefault();
            window.toggleItalic();
        }
        // Ctrl/Cmd + U (Underline)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'u') {
            e.preventDefault();
            window.toggleUnderline();
        }
        // Ctrl/Cmd + G (Group)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'g') {
            e.preventDefault();
            window.groupObjects();
        }
        // Ctrl/Cmd + Shift + G (Ungroup)
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'g') {
            e.preventDefault();
            window.ungroupObjects();
        }
        // Ctrl/Cmd + D (Duplicate)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
            e.preventDefault();
            window.duplicateObject();
        }
        // Ctrl/Cmd + Plus (Zoom In)
        if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '=')) {
            e.preventDefault();
            window.zoomCanvas('in');
        }
        // Ctrl/Cmd + Minus (Zoom Out)
        if ((e.ctrlKey || e.metaKey) && e.key === '-') {
            e.preventDefault();
            window.zoomCanvas('out');
        }
        // Arrow keys (move selected object)
        const obj = activeCanvas.getActiveObject();
        if (obj && ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
            e.preventDefault();
            let step = e.shiftKey ? 10 : 1;
            switch (e.key) {
                case 'ArrowLeft': obj.left -= step; break;
                case 'ArrowRight': obj.left += step; break;
                case 'ArrowUp': obj.top -= step; break;
                case 'ArrowDown': obj.top += step; break;
            }
            obj.setCoords();
            activeCanvas.renderAll();
        }
        // Esc (deselect)
        if (e.key === 'Escape') {
            activeCanvas.discardActiveObject();
            activeCanvas.renderAll();
        }
    });

    // --- Expose shared variables for other modules ---
    window.fabricCanvases = fabricCanvases;
    window.activeCanvas = activeCanvas;
    window.getCanvas = getCanvas;
    window.saveCanvasState = saveCanvasState;
    window.setupCanvasEvents = setupCanvasEvents;
    window.getActiveIText = getActiveIText;
    window.canvasStatesMap = canvasStatesMap;
    window.redoStackMap = redoStackMap;
    window.fonts = fonts;

    // Sync local activeCanvas with global on change
    document.addEventListener('activeCanvasChanged', function (e) {
        activeCanvas = e.detail && e.detail.canvas ? e.detail.canvas : window.activeCanvas;
    });
});
