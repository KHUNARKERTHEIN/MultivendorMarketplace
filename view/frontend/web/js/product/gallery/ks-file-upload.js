/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
/*jshint browser:true jquery:true*/
define([
     "jquery",
          'mage/template',
          'Magento_Ui/js/modal/alert',
          "mage/translate",
          "jquery/file-uploader"
      ], function ($, mageTemplate, alert) {
        'use strict';

    /**
     * Product gallery widget
     */
    $.widget('mage.ksfileupload', {
        options: {
            types: null
        },

        /**
         * Gallery creation
         * @protected
         */
        _create: function () {
            var self = this;

            self.ksFileUpload();
            self.ksFileUploadProcess();
        },

        /**
         * Image upload
         */
        ksFileUpload: function () {
            var self = this;

            $('#ksfileupload').fileupload({ 
                dataType: 'json',
                dropZone: '[data-tab-panel=image-management]',
                sequentialUploads: true,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: self.options.ksMaxFileSize ,
                add: function (e, data) {
                    var progressTmpl = mageTemplate('#media_gallery_content_Uploader-template'),
                      fileSize,
                      tmpl;

                    $.each(data.files, function (index, file) {
                      fileSize = typeof file.size == "undefined" ?
                          $.mage.__('We could not detect a size.') : file.size
                          //byteConvert(file.size);

                      data.fileId = Math.random().toString(33).substr(2, 18);

                      tmpl = progressTmpl({
                          data: {
                              name: file.name,
                              size: fileSize,
                              id: data.fileId
                          }
                      });

                      $(tmpl).appendTo('#media_gallery_content_Uploader');
                    });

                    $(this).fileupload('process', data).done(function () {
                      data.submit();
                    });
                },
                done: function (e, data) {
                    if (data.result && !data.result.error) {
                        $('#media_gallery_content').trigger('addItem', data.result);
                    } else {
                        $('#' + data.fileId)
                            .delay(2000)
                            .hide('highlight');
                        alert({
                            content: $.mage.__('We don\'t recognize or support this file extension type.')
                        });
                    }
                    $('#' + data.fileId).remove();
                },
                progress: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    var progressSelector = '#' + data.fileId + ' .ks-progressbar-container .ks-progressbar';
                    $(progressSelector).css('width', progress + '%');
                },
                fail: function (e, data) {
                    var progressSelector = '#' + data.fileId;
                    $(progressSelector).removeClass('ks-upload-progress').addClass('upload-failure')
                      .delay(2000)
                      .hide('highlight')
                      .remove();
                }
            });
        },

        /**
         * Image upload progress
         */
        ksFileUploadProcess: function () {
             var self = this;

            $('#ksfileupload').fileupload('option', {
                process: [{
                    action: 'load',
                    fileTypes: /^image\/(gif|jpeg|png)$/
                }, {
                    action: 'resize',
                    maxWidth: self.options.ksMaxWidth ,
                    maxHeight: self.options.ksMaxHeight
                }, {
                    action: 'save'
                }]
            });
         }

       
    });

    return $.mage.ksfileupload;
});
