jQuery(document).ready(function($) {
    $('#add-track').on('click', function() {
        var frame = wp.media({
            title: 'Select Audio File and Cover Image',
            button: {
                text: 'Use these files'
            },
            multiple: true,
            library: {
                type: ['audio', 'image']
            }
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var audioFile = attachments.find(file => file.type === 'audio');
            var imageFile = attachments.find(file => file.type === 'image');

            if (audioFile) {
                var data = {
                    action: 'bap_save_track',
                    title: audioFile.title,
                    audio_id: audioFile.id,
                    cover_id: imageFile ? imageFile.id : '',
                    nonce: bapAdminData.nonce
                };

                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        var newTrack = `
                            <div class="track-item">
                                <input type="text" name="track_title[]" value="${audioFile.title}" placeholder="Track Title">
                                <input type="text" name="track_artist[]" placeholder="Artist">
                                <input type="hidden" name="track_audio[]" value="${audioFile.url}">
                                <input type="hidden" name="track_cover[]" value="${imageFile ? imageFile.url : ''}">
                                <span>${audioFile.filename}</span>
                                ${imageFile ? `<img src="${imageFile.url}" alt="Cover" style="max-width: 50px; max-height: 50px;">` : '<button type="button" class="button add-cover">Add Cover</button>'}
                                <input type="hidden" name="track_id[]" value="${response.data.track_id}">
                                <button type="button" class="button remove-track">Remove</button>
                            </div>
                        `;
                        $('#better-audio-player-tracks').append(newTrack);
                    } else {
                        alert('Error saving track: ' + response.data.message);
                    }
                }).fail(function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    alert('Error saving track. Please check the console for more details.');
                });
            }
        });

        frame.open();
    });

    $(document).on('click', '.remove-track', function() {
        var trackItem = $(this).closest('.track-item');
        var trackId = trackItem.find('input[name="track_id[]"]').val();

        var data = {
            action: 'bap_remove_track',
            track_id: trackId,
            nonce: bapAdminData.nonce
        };

        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                trackItem.remove();
            } else {
                alert('Error removing track: ' + response.data.message);
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            alert('Error removing track. Please check the console for more details.');
        });
    });

    // Add this new event handler for adding/changing cover images
    $(document).on('click', '.add-cover, img[alt="Cover"]', function() {
        var trackItem = $(this).closest('.track-item');
        var trackId = trackItem.find('input[name="track_id[]"]').val();

        var frame = wp.media({
            title: 'Select Cover Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();

            var data = {
                action: 'bap_update_cover',
                track_id: trackId,
                cover_id: attachment.id,
                nonce: bapAdminData.nonce
            };

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    trackItem.find('input[name="track_cover[]"]').val(attachment.url);
                    trackItem.find('.add-cover, img[alt="Cover"]').replaceWith(`<img src="${attachment.url}" alt="Cover" style="max-width: 50px; max-height: 50px;">`);
                } else {
                    alert('Error updating cover: ' + response.data.message);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('Error updating cover. Please check the console for more details.');
            });
        });

        frame.open();
    });
});