<div class="modal fade" id="barcodeScannerModal" tabindex="-1" role="dialog" aria-labelledby="barcodeScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <h5 class="modal-title font-weight-bold" id="barcodeScannerModalLabel" style="color: #333;">
                    <i class="tio-camera mr-2" style="color: #149174;"></i>{{ 'Escáner de Código de Barras' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="stopBarcodeScanner()">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="padding: 20px;">
                <div id="barcode-reader-container" style="position: relative; width: 100%; max-width: 400px; margin: 0 auto; background-color: #000; border-radius: 12px; overflow: hidden; aspect-ratio: 1;">
                    <div id="barcode-reader" style="width: 100%;"></div>
                </div>
                <div class="mt-3">
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        {{ 'Apunte la cámara del celular al código de barras del producto.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script_2')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" type="text/javascript"></script>
<script>
    let html5QrCode = null;

    function openBarcodeScanner(targetInputId) {
        // Record which input field needs the value
        $('#barcodeScannerModal').data('target-input', targetInputId);
        
        // Show modal
        $('#barcodeScannerModal').modal('show');

        // Allow some time for the modal transition to complete before starting camera
        setTimeout(() => {
            try {
                if (html5QrCode) {
                    html5QrCode.clear();
                }
                
                html5QrCode = new Html5Qrcode("barcode-reader", {
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.QR_CODE
                    ]
                });
                
                const config = { 
                    fps: 20, 
                    qrbox: function(width, height) {
                        let widthBox = Math.floor(width * 0.85);
                        let heightBox = Math.floor(height * 0.35);
                        if (widthBox < 220) widthBox = 220;
                        if (heightBox < 90) heightBox = 90;
                        return { width: widthBox, height: heightBox };
                    },
                    aspectRatio: 1.0,
                    videoConstraints: {
                        facingMode: "environment",
                        width: { min: 640, ideal: 1280, max: 1920 },
                        height: { min: 480, ideal: 720, max: 1080 }
                    }
                };

                html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText, decodedResult) => {
                        const targetId = $('#barcodeScannerModal').data('target-input');
                        const targetElement = $('#' + targetId);
                        if (targetElement.length) {
                            targetElement.val(decodedText);
                            targetElement.trigger('change');
                            targetElement.trigger('input');
                            
                            // If POS search, submit the form automatically
                            if (targetId === 'datatableSearch') {
                                $('#search-form').submit();
                            }
                        }
                        stopBarcodeScanner();
                    },
                    (errorMessage) => {
                        // Silent fail for scanning frames
                    }
                ).catch((err) => {
                    console.error("Error starting barcode scanner camera stream: ", err);
                    toastr.error("{{ 'No se pudo acceder a la cámara. Verifica los permisos de tu navegador.' }}");
                    $('#barcodeScannerModal').modal('hide');
                });
            } catch (e) {
                console.error("Scanner exception: ", e);
            }
        }, 400);
    }

    function stopBarcodeScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                $('#barcodeScannerModal').modal('hide');
            }).catch((err) => {
                console.error("Failed to stop scanner: ", err);
                $('#barcodeScannerModal').modal('hide');
            });
        } else {
            $('#barcodeScannerModal').modal('hide');
        }
    }

    $(document).ready(function() {
        // Safe check: stop camera feed if modal is dismissed by user clicking outside or press Esc
        $('#barcodeScannerModal').on('hidden.bs.modal', function () {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                }).catch(err => console.error("Error closing scanner on modal hide: ", err));
            }
        });
    });
</script>
@endpush
