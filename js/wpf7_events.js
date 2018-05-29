jQuery(document).ready(function() {
    self.canSendRequest = true;
    var $ = jQuery;

    /*********************
    * Hook On wpf7 events (Reset)
    */
    document.addEventListener('onwpcf7mailsent', function(e) {
        if ('canSendRequest' in self)
            self.canSendRequest = true;
    }, false );

    document.addEventListener('wpcf7invalid', function(e) {
        if ('canSendRequest' in self) 
            self.canSendRequest = true;
    });

    /**********************************
    * Prevent multiple HTTP requests
    */
    $('.wpcf7-form-control.wpcf7-submit').click(function(e) {
        if ('canSendRequest' in self && self.canSendRequest === false) {
            return false;
        }
        else {
            self.canSendRequest = false;
        }
    });
})
