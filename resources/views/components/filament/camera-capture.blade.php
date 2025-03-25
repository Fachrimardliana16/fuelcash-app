<div x-data="simpleCameraCapture" class="mt-2 flex items-center justify-start">
    <input
        type="file"
        x-ref="cameraInput"
        accept="image/*"
        capture="environment"
        class="hidden"
        @change="handleCapture"
    >

    <button
        type="button"
        @click="$refs.cameraInput.click()"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-500 border border-primary-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-500 dark:hover:bg-primary-400 dark:border-primary-500 dark:text-white transition-colors duration-200"
    >
        <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M1 8a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 018.07 3h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0016.07 6H17a2 2 0 012 2v7a2 2 0 01-2 2H3a2 2 0 01-2-2V8zm13.5 3a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM10 14a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
        </svg>
        <span>Ambil Foto</span>
    </button>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('simpleCameraCapture', () => ({
            handleCapture(event) {
                const file = event.target.files[0];
                if (!file) {
                    console.log('No file selected');
                    return;
                }

                try {
                    // Find all visible file inputs that are not our camera input
                    const fileInputs = document.querySelectorAll('input[type="file"]:not([x-ref="cameraInput"])');

                    // Get the closest file input by traversing up the DOM
                    let targetInput = null;
                    let currentElement = this.$el;

                    while (currentElement && !targetInput) {
                        const nearestInput = Array.from(fileInputs).find(input =>
                            currentElement.contains(input) || input.contains(currentElement)
                        );
                        if (nearestInput) {
                            targetInput = nearestInput;
                            break;
                        }
                        currentElement = currentElement.parentElement;
                    }

                    if (targetInput) {
                        console.log('Found target input:', targetInput);

                        // Create new file list
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        targetInput.files = dt.files;

                        // Dispatch events
                        targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                        targetInput.dispatchEvent(new Event('input', { bubbles: true }));

                        // Find the closest form
                        const form = targetInput.closest('form');
                        if (form) {
                            // Get all buttons in the form
                            const buttons = form.querySelectorAll('button[type="submit"], button.fi-fo-upload-button');
                            const uploadButton = Array.from(buttons).find(button =>
                                button.textContent.toLowerCase().includes('upload') ||
                                button.classList.contains('fi-fo-upload-button')
                            );

                            if (uploadButton) {
                                setTimeout(() => {
                                    uploadButton.click();
                                    console.log('Triggered upload');
                                }, 100);
                            }
                        }
                    } else {
                        console.warn('Could not find target file input');
                    }
                } catch (error) {
                    console.error('Camera capture error:', error);
                }

                // Reset camera input
                event.target.value = '';
            }
        }));
    });
</script>
