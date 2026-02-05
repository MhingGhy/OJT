/**
 * Copy to Clipboard Functionality
 * Handles copying temporary credentials with visual feedback
 */

// Initialize copy buttons when DOM is ready
function initCopyButtons() {
    console.log('Initializing copy buttons...');
    const buttons = document.querySelectorAll('.copy-btn');
    console.log('Found ' + buttons.length + ' copy buttons');

    // Add click event listeners to all copy buttons
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const text = this.getAttribute('data-copy-text');
            console.log('Button clicked! Text to copy:', text);
            copyToClipboard(text, this);
        });
    });

    // Also handle copy-input-btn class (for add_trainee.php)
    const inputButtons = document.querySelectorAll('.copy-input-btn');
    console.log('Found ' + inputButtons.length + ' input copy buttons');

    inputButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target-id');
            const input = document.getElementById(targetId);
            if (input) {
                copyToClipboard(input.value, this);
            }
        });
    });
}

// Try both DOMContentLoaded and immediate execution
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCopyButtons);
} else {
    // DOM already loaded, execute immediately
    initCopyButtons();
}

function copyToClipboard(text, button) {
    try {
        console.log('Attempting to copy:', text);

        // Check if clipboard API is available
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text)
                .then(function () {
                    console.log('Copy successful (modern API)');
                    showSuccess(button);
                })
                .catch(function (err) {
                    console.error('Modern clipboard API failed:', err);
                    tryFallback();
                });
        } else {
            console.log('Modern clipboard API not available, using fallback');
            tryFallback();
        }

        function tryFallback() {
            try {
                // Create temporary textarea
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '0';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                const success = document.execCommand('copy');
                document.body.removeChild(textArea);

                if (success) {
                    console.log('Copy successful (fallback)');
                    showSuccess(button);
                } else {
                    console.error('execCommand returned false');
                    alert('Failed to copy. Please copy manually: ' + text);
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                alert('Failed to copy: ' + err.message);
            }
        }

        function showSuccess(btn) {
            if (btn) {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check"></i>';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');

                setTimeout(function () {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }
        }
    } catch (err) {
        console.error('Copy function error:', err);
        alert('Error: ' + err.message);
    }
}
