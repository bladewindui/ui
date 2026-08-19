{{-- format-ignore-start --}}
@use(Illuminate\Support\Str)
@props([
    // name of the input field for use in passing form submission data
    // this is prefixed with bw- when used as a class name
    'name' => defaultBladewindName('bw-file-'),

    // by default all file media and pdf file types can be selected
    'acceptedFileTypes' => config('bladewind.filepicker.accepted_file_types', 'image/*,audio/*,video/*,application/pdf'),

    // the default text to display in the file picker
    'placeholderLine1' => config('bladewind.filepicker.placeholder_line1', 'Choose files or drag and drop to upload'),
    'placeholderLine2' => config('bladewind.filepicker.placeholder_line2', '%s up to %s'),

    // ensure a file is selected by user
    'required' => false,

    // add files to the component by browsing for files
    'canBrowse' => config('bladewind.filepicker.can_browse', true),

    // add files using only drag and drop on the filepicker component
    'canDrop' => config('bladewind.filepicker.can_drop', true),

    // disable the filepicker
    'disabled' => false,

    // validate sizes of selected files
    'validateFileSize' => config('bladewind.filepicker.validate_file_size', true),

    // generate base64 output of selected files
    'base64' => config('bladewind.filepicker.base64', false),

    // how should the base64 data be returned. string | url
    'base64Output' => config('bladewind.filepicker.base64_output', 'url'),

    // show Filepond credits
    'showCredits' => config('bladewind.filepicker.show_credits', false),

    // should files be automatically uploaded to a server once selected
    'autoUpload' => config('bladewind.filepicker.auto_upload', false),

    // how many files can the user select
    'maxFiles'   => config('bladewind.filepicker.max_files', 1),

    // maximum size for each file
    'maxFileSize'   => config('bladewind.filepicker.max_file_size', '5mb'),

    // maximum size allowed for all files selected
    'maxTotalFileSize'   => config('bladewind.filepicker.max_total_file_size', null),

    // display selected files when component loads
    'selectedValue' => [],

    // when files exist, add new files to this position. top|bottom
    'addNewFilesTo' => config('bladewind.filepicker.add_new_files_to', 'top'),

    // show image previews when images are selected
    'showImagePreview' => config('bladewind.filepicker.show_image_preview', true),

    // allow image resizing in the background. This does not display any UI resizing controls
    'canResizeImage' => config('bladewind.filepicker.can_resize_image', false),

    // images should be resized to this width
    'imageResizeWidth' => config('bladewind.filepicker.image_resize_width', null),

    // images should be resized to this height
    'imageResizeHeight' => config('bladewind.filepicker.image_resize_height', null),

    // allow image cropping
    'canCrop' => config('bladewind.filepicker.can_crop', false),

    // aspect ratio used for image cropping
    'cropAspectRatio' => config('bladewind.filepicker.crop_aspect_ratio', '16:9'),

    // url for file uploads
    'uploadRoute' => null,

    // HTTP headers to append when calling $uploadRoute
    'uploadHeaders' => [],

    // HTTP method for uploading files
    'uploadMethod' => 'POST',

    // url for deleting uploaded files
    'deleteRoute' => null,

    // HTTP method for deleting uploaded files
    'deleteMethod' => null,

    // HTTP headers to append when calling $deleteRoute
    'deleteHeaders' => null,

    'nonce' => config('bladewind.script.nonce', null),
    // give the field its error state and render $errors->first() beneath it
    // note there is no fill_from_old here: a file input cannot be repopulated
    'showValidationError' => config('bladewind.forms.show_validation_error', false),

    // which error bag to read; null uses Laravel's default
    'errorBag' => config('bladewind.forms.error_bag', null),
])
@php
    $name = parseBladewindName($name);
    $cleanName = str_replace('[]', '', $name);
    $required = parseBladewindVariable($required);
    $canBrowse = parseBladewindVariable($canBrowse);
    $canDrop = parseBladewindVariable($canDrop);
    $canCrop = parseBladewindVariable($canCrop);
    $canResizeImage = parseBladewindVariable($canResizeImage);
    $validateFileSize = parseBladewindVariable($validateFileSize);
    $showImagePreview = parseBladewindVariable($showImagePreview);
    $autoUpload = parseBladewindVariable($autoUpload);
    $maxFileSize = ((Str::contains($maxFileSize, 'b')) ? $maxFileSize : $maxFileSize.'mb') ;
    $maxFiles = (! is_numeric($maxFiles)) ? 1 : (int) $maxFiles;
    $imageResizeWidth = (! is_numeric($imageResizeWidth)) ? null : $imageResizeWidth;
    $imageResizeHeight = (! is_numeric($imageResizeHeight)) ? null : $imageResizeHeight;
    $hasImageFiles = Str::contains($acceptedFileTypes,['image','png','jpg','jpeg','gif']);
    $cropAspectRatio = isValidAspectRatio($cropAspectRatio) ? $cropAspectRatio : 'NaN';
    if(!app()->environment('production') && !file_exists('vendor/bladewind/css/filepond.min.css')) {
        echo '<span class="text-red-400">filepicker assets missing. <a href="https://bladewindui.com/install#install">publish</a> public bladewind assets</span>';
    }
    $deleteRoute = empty($deleteRoute) ? $uploadRoute : $deleteRoute;
    $deleteMethod = empty($deleteMethod) ? $uploadMethod : $deleteMethod;
    $uploadHeaders = is_array($uploadHeaders) ? $uploadHeaders : json_decode($uploadHeaders, true);
    $deleteHeaders = empty($deleteHeaders) ? $uploadHeaders : (is_array($deleteHeaders) ? $deleteHeaders : json_decode($deleteHeaders, true));
@endphp
@once
    <link href="{{ asset('vendor/bladewind/css/filepond.css') }}" rel="stylesheet"/>
    @if($hasImageFiles)
    <link href="{{ asset('vendor/bladewind/css/filepond-plugin-image-preview.css') }}" rel="stylesheet"/>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-image-exif-orientation.js') }}"></x-bladewind::script>
    <link href="{{ asset('vendor/bladewind/css/cropper.min.css') }}" rel="stylesheet">
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/cropper.min.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-image-crop.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-image-preview.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-image-resize.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-image-transform.js') }}"></x-bladewind::script>
    @endif
    @if($validateFileSize)
        <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-file-validate-size.js') }}"></x-bladewind::script>
        @if($hasImageFiles)<x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-image-validate-size.js') }}"></x-bladewind::script>@endif
    @endif
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-file-encode.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond-plugin-file-validate-type.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/filepond.min.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce">const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "{{ csrf_token() }}";</x-bladewind::script>
@endonce
{{-- format-ignore-end --}}

@if($canCrop)
    @once
        <x-bladewind::modal name="cropper-container" size="large" align_buttons="center" backdrop_can_close="false" ok_button_label="Crop">
            <img class="cropper-image max-w-full" />
        </x-bladewind::modal>
    @endonce
@endif

<div class="bw-filepicker-placeholder-{{$cleanName}} space-y-2 flex hidden align-middle py-3">
    <div>
        <x-bladewind::icon name="note-2" class="size-14 rounded-full p-2 bg-gray-200/50 stroke-2 text-slate-400 dark:bg-dark-100/50 dark:invert dark:opacity-30"/>
    </div>
    <div class="text-left pl-2.5 pt-1.5 dark:text-dark-200">
        <div>{!! $placeholderLine1 !!} @if($required)<x-bladewind::icon name="star" class="text-red-400! size-2! mt-[-2px]" type="solid"/>@endif</div>
        <div class="text-xs! tracking-wider opacity-70">
        {{
            sprintf($placeholderLine2,
            strtoupper(preg_replace(['/\/\*/', '/application\//', '/,/', '/\s*,\s*/', '/\./'], ['', '', ', ', ', '], $acceptedFileTypes)),
            strtoupper($maxFileSize))
        }}
        </div>
    </div>
</div>

<input type="file" name="{{$name}}" accept="{{$acceptedFileTypes}}" @if($required) required="required" @endif />
@if(bladewindValidationError($name, parseBladewindVariable($showValidationError), $errorBag) !== '')
    <div class="text-red-500 text-xs p-1 {{ $name }}-validation-error">{{ bladewindValidationError($name, true, $errorBag) }}</div>
@endif
@if($base64)<div class="{{$cleanName}}-b64-container hidden"></div>@endif

<x-bladewind::script :nonce="$nonce">
@if($canCrop) @once
    const cropperContainer = domEl('.cropper-container');
    const cropperImage = domEl('.cropper-image');
    const cropButton = domEl('.bw-cropper-container-modal .okay');
    let cropper;
@endonce @endif
    FilePond.registerPlugin(
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType,
        @if($hasImageFiles)
        FilePondPluginImageExifOrientation,
        FilePondPluginImageValidateSize,
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginImageTransform,
        @if($canCrop)FilePondPluginImageCrop,@endif
        @endif
        @if($base64)FilePondPluginFileEncode,@endif
    );
const pond_{{$cleanName}} = FilePond.create(domEl('input[name="{{$name}}"]'), {
        name: '{{$cleanName}}',
        className: '{{$cleanName}}',
        maxFiles: {{$maxFiles}},
        maxFileSize: '{{$maxFileSize}}',
        allowFileSizeValidation: {{$validateFileSize ? 'true' : 'false'}},
        maxTotalFileSize: {{!empty($maxTotalFileSize) ? "'$maxTotalFileSize'" : 'null'}},
        allowMultiple: {{$maxFiles > 1 ? 'true' : 'false'}},
        allowBrowse: {{$canBrowse ? 'true' : 'false'}},
        allowDrop: {{$canDrop ? 'true' : 'false'}},
        @if($base64)allowFileEncode: true,@endif
        @if($hasImageFiles)
        @if($canCrop)allowImageCrop: {{$canCrop ? 'true' : 'false'}},@endif
        allowImagePreview: {{$showImagePreview ? 'true' : 'false'}},
        allowImageResize: {{$canResizeImage ? 'true' : 'false'}},
        imageEditAllowEdit: true,
        imageResizeTargetWidth: {{empty($imageResizeWidth) ? 'null' : $imageResizeWidth}},
        imageResizeTargetHeight: {{empty($imageResizeHeight) ? 'null' : $imageResizeHeight}},
        @endif
        disabled: {{$disabled ? 'true' : 'false'}},
        itemInsertLocation: '{{$addNewFilesTo == 'top' ? 'before' : 'after'}}',
        credits: {{$showCredits ? 'true' : 'false'}},
        labelIdle: (domEl(".placeholder-{{$cleanName}}")) ? domEl(".placeholder-{{$cleanName}}").innerHTML : domEl(".bw-filepicker-placeholder-{{$cleanName}}").innerHTML,
        acceptedFileTypes: {!! json_encode(explode(',', str_replace(' ', '', $acceptedFileTypes))) !!},
        labelInvalidField: '{{ __("bladewind::bladewind.filepicker_invalid_field")}}',
        labelFileWaitingForSize: '{{ __("bladewind::bladewind.filepicker_waiting_for_size")}}',
        labelFileSizeNotAvailable: '{{ __("bladewind::bladewind.filepicker_file_size_not_available")}}',
        labelFileLoading: '{{ __("bladewind::bladewind.filepicker_file_loading")}}',
        labelFileLoadError: '{{ __("bladewind::bladewind.filepicker_file_load_error")}}',
        labelFileProcessing: '{{ __("bladewind::bladewind.filepicker_file_processing")}}',
        labelFileProcessingComplete: '{{ __("bladewind::bladewind.filepicker_file_processing_complete")}}',
        labelFileProcessingAborted: '{{ __("bladewind::bladewind.filepicker_file_processing_aborted")}}',
        labelFileProcessingError: '{{ __("bladewind::bladewind.filepicker_file_processing_error")}}',
        labelFileProcessingRevertError: '{{ __("bladewind::bladewind.filepicker_file_processing_revert_error")}}',
        labelFileRemoveError: '{{ __("bladewind::bladewind.filepicker_file_remove_error")}}',
        labelTapToCancel: '{{ __("bladewind::bladewind.cancel")}}',
        labelTapToRetry: '{{ __("bladewind::bladewind.retry")}}',
        labelTapToUndo: '{{ __("bladewind::bladewind.undo")}}',
        labelButtonRemoveItem: '{{ __("bladewind::bladewind.remove")}}',
        labelButtonAbortItemLoad: '{{ __("bladewind::bladewind.abort")}}',
        labelButtonRetryItemLoad: '{{ __("bladewind::bladewind.retry")}}',
        labelButtonAbortItemProcessing: '{{ __("bladewind::bladewind.cancel")}}',
        labelButtonUndoItemProcessing: '{{ __("bladewind::bladewind.undo")}}',
        labelButtonRetryItemProcessing: '{{ __("bladewind::bladewind.retry")}}',
        labelButtonProcessItem: '{{ __("bladewind::bladewind.upload")}}',
        labelMaxFileSizeExceeded: '{{ __("bladewind::bladewind.filepicker_max_file_size_exceeded")}}',
        labelMaxTotalFileSizeExceeded: '{{ __("bladewind::bladewind.filepicker_max_total_file_size_exceeded")}}',
        labelMaxFileSize: '{{__("bladewind::bladewind.filepicker_max_file_size")}}',
        labelMaxTotalFileSize: '{{__("bladewind::bladewind.filepicker_max_total_file_size")}}',
        labelFileTypeNotAllowed: '{{__("bladewind::bladewind.filepicker_invalid_file_type")}}',
        fileValidateTypeLabelExpectedTypes: '{{__("bladewind::bladewind.filepicker_expected_file_types")}}',
        fileValidateTypeLabelExpectedTypesMap: {
            'image/*': 'images',
            'audio/*': 'audios',
            'video/*': 'videos',
            'application/msword': 'word docs',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'word docs',
            'video/*': 'videos',
            'application/json': 'json',
            'application/pdf': 'pdfs'
        },
        iconRemove: `<x-bladewind::icon name="trash" class="p-1 opacity-80 hover:text-red-500" type="solid" />`,
        @if($autoUpload && !empty($uploadRoute))
            server: {
                process: {
                    url: "{{$uploadRoute}}",
                    method: "{{$uploadMethod}}",
                    headers: {
                        @if(!empty($uploadHeaders))
                            @foreach($uploadHeaders as $key => $value)
                                "{{ $key }}": "{{ $value }}",
                            @endforeach
                        @endif
                        "X-CSRF-TOKEN": `${csrfToken}`
                    },
                },
                revert: {
                    url: "{{$deleteRoute}}",
                    method: "{{$deleteMethod}}",
                    headers: {
                        @if(!empty($deleteHeaders))
                            @foreach($deleteHeaders as $key => $value)
                                "{{ $key }}": "{{ $value }}",
                            @endforeach
                        @endif
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": `${csrfToken}`
                    },
                },
            }
        @else
            storeAsFile: true,
        @endif
        @if(!empty($selectedValue))
            files: @json($selectedValue),
            allowRevert: true,
        @endif
    });
@if($canCrop || $base64)
    pond_{{$cleanName}}.on('addfile', (error, fileItem) => {
        if (error) return;
        @if($canCrop)
            const file = fileItem.file;
            if (file.isCropped || !file.type.startsWith("image/")) return;

            const reader = new FileReader();

            reader.onload = () => {
                cropperImage.src = reader.result;
                showModal('cropper-container');

                if (cropper) cropper.destroy();

                cropper = new Cropper(cropperImage, {
                    viewMode: 1,
                    autoCrop: true,
                    aspectRatio: {!! str_replace(':','/', "$cropAspectRatio") !!},
                });

                cropButton.onclick = () => {
                    const canvas = cropper.getCroppedCanvas();
                    if (!canvas) return;

                    canvas.toBlob((blob) => {
                        if (!blob) return;

                        const croppedFile = new File([blob], file.name, {
                            type: file.type,
                            lastModified: Date.now(),
                        });

                        croppedFile.isCropped = true;
                        hideModal('cropper-container');

                        pond_{{$name}}.removeFile(fileItem.id);
                        pond_{{$name}}.addFile(croppedFile).then(() => {
                            cropper.destroy();
                            cropper = null;
                        });
                    }, file.type);
                };
            };
            reader.readAsDataURL(file);
        @endif
        @if($base64)
            const base64_container = domEl(".{{$cleanName}}-b64-container");
            const base64 = {{$base64Output == 'string' ? 'fileItem.getFileEncodeBase64String()' : 'fileItem.getFileEncodeDataURL()'}};

            if (!base64_container.querySelector(`input[data-id="${fileItem.id}"]`)) {
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = "{{$cleanName}}_b64[]"
                hiddenInput.value = base64;
                hiddenInput.dataset.id = fileItem.id; // Store FilePond file ID
                base64_container.appendChild(hiddenInput);
            }
        @endif
    });
    @endif
</x-bladewind::script>