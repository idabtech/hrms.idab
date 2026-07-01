{{-- Sidebar File Menu --}}
<li class="dash-item dash-hasmenu menu-tab-li active dash-trigger">
    <a href="javascript:void(0)" class="dash-link" onclick="showMenuTab('file-menu')">
        <span class="dash-micon">📁</span>
        <span class="dash-mtext">{{ __('File') }}</span>
        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
    </a>
    <ul id="file-menu" class="dash-submenu menu-panel active" style="display: block;">
        <li class="dash-item"><a href="javascript:void(0)" onclick="exportCanvasAsImage()" class="dash-link">💾
                {{ __('Export as Image') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="resetCanvas()" class="dash-link">♻️
                {{ __('Reset Canvas') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="downloadPDF()" class="dash-link">⬇️
                {{ __('Download PDF') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="printPDF()" class="dash-link">🖨️
                {{ __('Print PDF') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addPage()" class="dash-link">➕
                {{ __('Add Page') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="deletePage()" class="dash-link">🗑️
                {{ __('Delete Page') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="duplicatePage()" class="dash-link">📄
                {{ __('Duplicate Page') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="movePageUp()" class="dash-link">⬆️
                {{ __('Move Page Up') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="movePageDown()" class="dash-link">⬇️
                {{ __('Move Page Down') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="jumpToPage()" class="dash-link">🔎
                {{ __('Go to Page') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addPageHeader()" class="dash-link">🔝
                {{ __('Page Header') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addPageTitle()" class="dash-link">📝
                {{ __('Page Title') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addWatermark()" class="dash-link">💧
                {{ __('Watermark') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addPageFooter()" class="dash-link">🔚
                {{ __('Page Footer') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="setPageBackgroundImage()" class="dash-link">🖼️
                {{ __('Background Image') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="setPageBackgroundFromFile()" class="dash-link">📁
                {{ __('Background from File') }}</a></li>
    </ul>
</li>

{{-- Sidebar Edit Menu --}}
<li class="dash-item dash-hasmenu menu-tab-li">
    <a href="javascript:void(0)" class="dash-link" onclick="showMenuTab('edit-menu')">
        <span class="dash-micon">✏️</span>
        <span class="dash-mtext">{{ __('Edit') }}</span>
        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
    </a>
    <ul id="edit-menu" class="dash-submenu menu-panel" style="display: none;">
        <li class="dash-item"><a href="javascript:void(0)" onclick="undo()" class="dash-link">↩️
                {{ __('Undo') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="redo()" class="dash-link">↪️
                {{ __('Redo') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="copyObject()" class="dash-link">📋
                {{ __('Copy') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="cutObject()" class="dash-link">✂️
                {{ __('Cut') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="pasteObject()" class="dash-link">📋
                {{ __('Paste') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="deleteSelected()" class="dash-link">🗑️
                {{ __('Delete') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="duplicateObject()" class="dash-link">📑
                {{ __('Duplicate') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="duplicateMultiple()" class="dash-link">📑
                {{ __('Duplicate xN') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="findReplace()" class="dash-link">🔍
                {{ __('Find/Replace') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="findReplaceAllPages()" class="dash-link"
                title="Find and replace text (All Pages)">🔍 {{ __('Find/Replace (All Pages)') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="clearAllObjects()" class="dash-link">🧹
                {{ __('Clear All') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="selectAllObjects()" class="dash-link">🔲
                {{ __('Select All') }}</a></li>
    </ul>
</li>

<li class="dash-item dash-hasmenu menu-tab-li">
    <a href="javascript:void(0)" class="dash-link" onclick="showMenuTab('insert-menu')">
        <span class="dash-micon">➕</span>
        <span class="dash-mtext">{{ __('Insert') }}</span>
        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
    </a>

    <ul id="insert-menu" class="dash-submenu menu-panel" style="display: none;">
        <li class="dash-item"><a href="javascript:void(0)" onclick="addText()"
                class="dash-link">{{ __('Text') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addTextbox()"
                class="dash-link">{{ __('Textbox') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addImage()"
                class="dash-link">{{ __('Image') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addImageFromUrl()"
                class="dash-link">{{ __('Image from URL') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addTable()"
                class="dash-link">{{ __('Table') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addBulletList()"
                class="dash-link">{{ __('Bulleted List') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addNumberList()"
                class="dash-link">{{ __('Numbered List') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addCheckboxList()"
                class="dash-link">{{ __('Checkbox List') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addDashList()"
                class="dash-link">{{ __('Dash List') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addRomanList()"
                class="dash-link">{{ __('Roman List') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addAlphaList()"
                class="dash-link">{{ __('Alpha List') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addQRCode()"
                class="dash-link">{{ __('QR Code') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addBarcode()"
                class="dash-link">{{ __('Barcode') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="togglePageNumber(this)"
                class="dash-link">{{ __('Add Page Numbers') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addEllipse()"
                class="dash-link">{{ __('Ellipse') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addPolygon()"
                class="dash-link">{{ __('Polygon') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addArrow()"
                class="dash-link">{{ __('Arrow') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addHighlight()"
                class="dash-link">{{ __('Highlight') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addCallout()"
                class="dash-link">{{ __('Callout') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addRuler()"
                class="dash-link">{{ __('Ruler') }}</a></li>
    </ul>
</li>

<li class="dash-item dash-hasmenu menu-tab-li">
    <a href="javascript:void(0)" class="dash-link" onclick="showMenuTab('format-menu')">
        <span class="dash-micon">🎨</span>
        <span class="dash-mtext">{{ __('Format') }}</span>
        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
    </a>

    <ul id="format-menu" class="dash-submenu menu-panel" style="display: none;">
        <li class="dash-item"><a href="javascript:void(0)" onclick="toggleBold()"
                class="dash-link font-bold">{{ __('Bold') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="toggleItalic()"
                class="dash-link italic">{{ __('Italic') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="toggleUnderline()"
                class="dash-link underline">{{ __('Underline') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="toggleStrikethrough()"
                class="dash-link line-through">{{ __('Strikethrough') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="toggleSuperscript()"
                class="dash-link">{{ __('Superscript') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="toggleSubscript()"
                class="dash-link">{{ __('Subscript') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="highlightText()"
                class="dash-link">{{ __('Highlight') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="insertDateTime()"
                class="dash-link">{{ __('Date/Time') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="insertHyperlink()"
                class="dash-link">{{ __('Link') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="updateFontSize()"
                class="dash-link">{{ __('Font Size') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="alignText('left')"
                class="dash-link">{{ __('Align Left') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="alignText('center')"
                class="dash-link">{{ __('Align Center') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="alignText('right')"
                class="dash-link">{{ __('Align Right') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="alignText('justify')"
                class="dash-link">{{ __('Justify') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="bringToFront()"
                class="dash-link">{{ __('Bring to Front') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="sendToBack()"
                class="dash-link">{{ __('Send to Back') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="updateFontColor()"
                class="dash-link">{{ __('Font Color') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="updateTextBgColor()"
                class="dash-link">{{ __('Text BG Color') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="updateFontFamily()"
                class="dash-link">{{ __('Font Family') }}</a></li>
    </ul>
</li>

<li class="dash-item dash-hasmenu menu-tab-li">
    <a href="javascript:void(0)" class="dash-link" onclick="showMenuTab('shapes-menu')">
        <span class="dash-micon">🔷</span>
        <span class="dash-mtext">{{ __('Shapes') }}</span>
        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
    </a>

    <ul id="shapes-menu" class="dash-submenu menu-panel" style="display: none;">
        <li class="dash-item"><a href="javascript:void(0)" onclick="drawShape('rect')"
                class="dash-link">{{ __('Rectangle') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="drawShape('circle')"
                class="dash-link">{{ __('Circle') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="drawShape('line')"
                class="dash-link">{{ __('Line') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="drawShape('arrow')"
                class="dash-link">{{ __('Arrow') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addHorizontalLine()"
                class="dash-link">{{ __('Horizontal Line') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addVerticalLine()"
                class="dash-link">{{ __('Vertical Line') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addStar()"
                class="dash-link">{{ __('Star') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addCheckmark()"
                class="dash-link">{{ __('Checkmark') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addCrossmark()"
                class="dash-link">{{ __('Crossmark') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="toggleFreeDraw()"
                class="dash-link">{{ __('Free Draw') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="eraseObject()"
                class="dash-link">{{ __('Eraser') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addTriangle()"
                class="dash-link">{{ __('Triangle') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addHexagon()"
                class="dash-link">{{ __('Hexagon') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addPentagon()"
                class="dash-link">{{ __('Pentagon') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addOctagon()"
                class="dash-link">{{ __('Octagon') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addHeart()"
                class="dash-link">{{ __('Heart') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addCloud()"
                class="dash-link">{{ __('Cloud') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addBarChart()"
                class="dash-link">{{ __('Bar Chart') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addPieChart()"
                class="dash-link">{{ __('Pie Chart') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addLineChart()"
                class="dash-link">{{ __('Line Chart') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addAreaChart()"
                class="dash-link">{{ __('Area Chart') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addScatterChart()"
                class="dash-link">{{ __('Scatter Chart') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addRoundedRect()"
                class="dash-link">{{ __('Rounded Rect') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addParallelogram()"
                class="dash-link">{{ __('Parallelogram') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addDiamond()"
                class="dash-link">{{ __('Diamond') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addCrescent()"
                class="dash-link">{{ __('Crescent') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addBookmark()"
                class="dash-link">{{ __('Bookmark') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addBracket()"
                class="dash-link">{{ __('Bracket') }}</a></li>
        <li class="dash-item"><a href="javascript:void(0)" onclick="addCurlyBrace()"
                class="dash-link">{{ __('Curly Brace') }}</a></li>
    </ul>
</li>

<!-- Object Menu -->
<li class="dash-item dash-hasmenu menu-tab-li">
    <a href="javascript:void(0)" class="dash-link" onclick="showMenuTab('object-menu')">
        <span class="dash-micon">🧩</span>
        <span class="dash-mtext">Object</span>
        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
    </a>

    <ul id="object-menu" class="dash-submenu menu-panel" style="display: none;">
        <div class="menu-btn-row">

            <li class="dash-item"><a href="javascript:void(0)" onclick="bringForward()"
                    class="dash-link">{{ __('Bring Forward') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="sendBackward()" class="dash-link">
                    {{ __('Send Backward') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="toggleLock()"
                    class="dash-link">{{ __('Lock/Unlock') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="changeFillColor()"
                    class="dash-link">{{ __('Fill Color') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="changeStrokeColor()"
                    class="dash-link">{{ __('Stroke Color') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="changeStrokeWidth()"
                    class="dash-link">{{ __('Stroke Width') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="changeOpacity()"
                    class="dash-link">{{ __('Opacity') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="mirrorObject()"
                    class="dash-link">{{ __('Mirror') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="lockAllObjects()"
                    class="dash-link">{{ __('Lock All') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="unlockAllObjects()"
                    class="dash-link">{{ __('Unlock All') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="pickColor()"
                    class="dash-link">{{ __('Color Picker') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="duplicateStyle()"
                    class="dash-link">{{ __('Copy Style') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="pasteStyle()"
                    class="dash-link">{{ __('Paste Style') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="alignObjects('left')"
                    class="dash-link">{{ __('Align Left') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="alignObjects('right')"
                    class="dash-link">{{ __('Align Right') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="alignObjects('top')"
                    class="dash-link">{{ __('Align Top') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="alignObjects('bottom')"
                    class="dash-link">{{ __('Align Bottom') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="alignObjects('center')"
                    class="dash-link">{{ __('Align Center') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="alignObjects('middle')"
                    class="dash-link">{{ __('Align Middle') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="distributeObjects('h')"
                    class="dash-link">{{ __('Distribute Horizontally') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="distributeObjects('v')"
                    class="dash-link">{{ __('Distribute Vertically') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="nudgeObject('left')"
                    class="dash-link">{{ __('Nudge Left') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="nudgeObject('right')"
                    class="dash-link">{{ __('Nudge Right') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="nudgeObject('up')"
                    class="dash-link">{{ __('Nudge Up') }}</a>
            <li class="dash-item"><a href="javascript:void(0)" onclick="nudgeObject('down')"
                    class="dash-link">{{ __('Nudge Down') }}</a>
        </div>
    </ul>
</li>

<li class="dash-item">
    <a class="dash-link" onclick="showMenuTab('image-menu')"><span class="dash-micon">🖼️</span><span
            class="dash-mtext">Image</span></a>
</li>
<li class="dash-item">
    <a class="dash-link" onclick="showMenuTab('special-menu')"><span class="dash-micon">⭐</span><span
            class="dash-mtext">Special</span></a>
</li>
<li class="dash-item">
    <a class="dash-link" onclick="showMenuTab('view-menu')"><span class="dash-micon">👁️</span><span
            class="dash-mtext">View</span></a>
</li>
<!-- Menu Panels as Horizontal Button Groups -->
<div class="w-full px-1 py-2">
    <!-- Image Menu -->
    <div id="image-menu" class="menu-panel" style="display:none;">
        <div class="menu-btn-row">
            <button onclick="applyGrayscale()" class="menu-btn bg-gray-100 hover:bg-gray-200 text-gray-800"><span
                    class="dash-micon">🌑</span><span class="dash-mtext">Grayscale</span></button>
            <button onclick="applyInvert()" class="menu-btn bg-gray-100 hover:bg-gray-200 text-gray-800"><span
                    class="dash-micon">🔄</span><span class="dash-mtext">Invert</span></button>
            <button onclick="removeImageFilters()" class="menu-btn bg-gray-100 hover:bg-gray-200 text-gray-800"><span
                    class="dash-micon">🚫</span><span class="dash-mtext">Remove
                    Filters</span></button>
            <button onclick="flipImageX()" class="menu-btn bg-blue-50 hover:bg-blue-100 text-blue-800"><span
                    class="dash-micon">↔️</span><span class="dash-mtext">Flip X</span></button>
            <button onclick="flipImageY()" class="menu-btn bg-blue-50 hover:bg-blue-100 text-blue-800"><span
                    class="dash-micon">↕️</span><span class="dash-mtext">Flip Y</span></button>
            <button onclick="rotateObject()" class="menu-btn bg-yellow-100 hover:bg-yellow-200 text-yellow-800"><span
                    class="dash-micon">🔄</span><span class="dash-mtext">Rotate</span></button>
            <button onclick="cropImage()" class="menu-btn bg-green-100 hover:bg-green-200 text-green-800"><span
                    class="dash-micon">✂️</span><span class="dash-mtext">Crop</span></button>
            <button onclick="resizeObject()" class="menu-btn bg-blue-100 hover:bg-blue-200 text-blue-800"><span
                    class="dash-micon">📐</span><span class="dash-mtext">Resize</span></button>
            <button onclick="replaceImage()" class="menu-btn bg-blue-100 hover:bg-blue-200 text-blue-800"><span
                    class="dash-micon">🔄</span><span class="dash-mtext">Replace Image</span></button>
        </div>
    </div>
    <!-- Special Menu -->
    <div id="special-menu" class="menu-panel" style="display:none;">
        <div class="menu-btn-row">
            <button onclick="addStamp()" class="menu-btn bg-green-200 hover:bg-green-300 text-green-900"><span
                    class="dash-micon">✅</span><span class="dash-mtext">Stamp</span></button>
            <button onclick="addSignature()" class="menu-btn bg-blue-200 hover:bg-blue-300 text-blue-900"><span
                    class="dash-micon">✍️</span><span class="dash-mtext">Signature</span></button>
            <button onclick="addStickyNote()" class="menu-btn bg-yellow-100 hover:bg-yellow-200 text-yellow-800"><span
                    class="dash-micon">📝</span><span class="dash-mtext">Sticky Note</span></button>
            <button onclick="addCircleStamp()" class="menu-btn bg-green-100 hover:bg-green-200 text-green-800"><span
                    class="dash-micon">🔵</span><span class="dash-mtext">Circle Stamp</span></button>
        </div>
    </div>
    <!-- View Menu -->
    <div id="view-menu" class="menu-panel" style="display:none;">
        <div class="menu-btn-row">
            <button onclick="zoomCanvas('in')" class="menu-btn bg-teal-100 hover:bg-teal-200 text-teal-800"><span
                    class="dash-micon">➕</span><span class="dash-mtext">Zoom
                    In</span></button>
            <button onclick="zoomCanvas('out')" class="menu-btn bg-teal-100 hover:bg-teal-200 text-teal-800"><span
                    class="dash-micon">➖</span><span class="dash-mtext">Zoom
                    Out</span></button>
            <button onclick="toggleRuler()" class="menu-btn bg-gray-100 hover:bg-gray-200 text-gray-800"><span
                    class="dash-micon">📏</span><span class="dash-mtext">Ruler</span></button>
            <button onclick="toggleGrid()" class="menu-btn bg-gray-100 hover:bg-gray-200 text-gray-800"><span
                    class="dash-micon">#</span><span class="dash-mtext">Grid</span></button>
        </div>
    </div>
</div>

<!-- Tool Containers -->
<div id="tool-pdf" class="mt-4">
    <!-- PDF editor canvas will be rendered here (already handled by JS) -->
</div>
<div id="tool-word" class="mt-4" style="display:none;">
    <textarea id="word-area" class="w-full h-64 border rounded p-2" placeholder="Type like Word..."></textarea>
</div>
<div id="tool-excel" class="mt-4" style="display:none;">
    <table id="excel-table" class="border w-full bg-white">
        <tbody>
            <tr>
                <td contenteditable="true" class="border px-2 py-1"></td>
            </tr>
        </tbody>
    </table>
</div>
<div id="tool-notepad" class="mt-4" style="display:none;">
    <textarea id="notepad-area" class="w-full h-64 border rounded p-2" placeholder="Simple Notepad..."></textarea>
</div>
<div id="tool-paint" class="mt-4" style="display:none;">
    <canvas id="paint-canvas" width="600" height="300" class="border bg-white rounded"></canvas>
</div>
