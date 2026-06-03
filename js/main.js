// =============================================
// SNIPPET VAULT — main.js
// Handles: live search, language filter,
//          copy to clipboard, dynamic file blocks,
//          tab switching on view page
// =============================================


// ---- LIVE SEARCH ----
const searchInput = document.getElementById('search');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.snippet-row').forEach(function (row) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? 'block' : 'none';
        });
    });
}


// ---- LANGUAGE FILTER ----
const langFilter = document.getElementById('lang-filter');

if (langFilter) {
    langFilter.addEventListener('change', function () {
        const selected = this.value.toLowerCase();
        document.querySelectorAll('.snippet-row').forEach(function (row) {
            const lang = row.getAttribute('data-language').toLowerCase();
            row.style.display = (selected === '' || lang === selected) ? 'block' : 'none';
        });
    });
}


// ---- COPY TO CLIPBOARD ----
// Uses event delegation — listens on document so it works for
// dynamically added copy buttons too (not just ones on page load)
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('copy-btn')) {
        const btn = e.target;

        // Find the <code> tag inside the active tab panel (or the only code block)
        const panel = btn.closest('.tab-panel') || btn.closest('.code-block');
        const code  = panel.querySelector('code').innerText;

        navigator.clipboard.writeText(code).then(function () {
            btn.innerText = 'Copied!';
            setTimeout(function () { btn.innerText = 'Copy'; }, 2000);
        });
    }
});


// ---- TAB SWITCHING (view page) ----
// Clicking a tab shows its panel and hides the others
const tabButtons = document.querySelectorAll('.tab-btn');

if (tabButtons.length > 0) {
    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Get which tab index was clicked from data-tab attribute
            const target = this.getAttribute('data-tab');

            // Remove active class from all tabs and panels
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

            // Add active class to the clicked tab and its matching panel
            this.classList.add('active');
            document.getElementById('panel-' + target).classList.add('active');

            // Re-run Prism highlighting on the newly visible panel
            // (Prism only highlights visible elements by default)
            Prism.highlightAllUnder(document.getElementById('panel-' + target));
        });
    });
}


// ---- DYNAMIC FILE BLOCKS (create / edit forms) ----

// Counter tracks how many file blocks exist so each gets a unique index
// We read the current count from existing blocks on the page
// (important for edit.php where blocks are pre-loaded from DB)
let fileCount = document.querySelectorAll('.file-block').length;

const addFileBtn   = document.getElementById('add-file');
const filesContainer = document.getElementById('files-container');

if (addFileBtn) {
    addFileBtn.addEventListener('click', function () {

        // Build a new file block with the next index number
        const block = document.createElement('div');
        block.className = 'file-block';
        block.innerHTML = `
            <div class="file-block-header">
                <!-- name="filename[]" — the [] makes PHP collect all filenames as an array -->
                <input type="text" name="filename[]" placeholder="e.g. style.css" required>
                <!-- Remove button — only visible if more than one block exists -->
                <button type="button" class="btn btn-danger remove-file">Remove</button>
            </div>
            <!-- name="code[]" — PHP collects all code blocks as an array -->
            <textarea name="code[]" placeholder="Paste your code here..." required></textarea>
        `;

        filesContainer.appendChild(block);
        fileCount++;

        // Re-run remove button visibility check after adding
        updateRemoveButtons();
    });
}

// ---- REMOVE FILE BLOCK ----
// Uses event delegation on the container so it works for dynamically added blocks
if (filesContainer) {
    filesContainer.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-file')) {
            e.target.closest('.file-block').remove();
            fileCount--;
            updateRemoveButtons();
        }
    });
}

// ---- UPDATE REMOVE BUTTON VISIBILITY ----
// Hide the remove button when only one file block remains
// (you must have at least one file)
function updateRemoveButtons() {
    const blocks = document.querySelectorAll('.file-block');
    blocks.forEach(function (block) {
        const btn = block.querySelector('.remove-file');
        if (btn) {
            // If only 1 block left, hide the remove button
            btn.style.display = blocks.length <= 1 ? 'none' : 'inline-block';
        }
    });
}

// Run on page load to set initial state
updateRemoveButtons();
