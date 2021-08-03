<!-- select2 from ajax -->
@php
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;
    // by default set ajax query delay to 500ms
    // this is the time we wait before send the query to the search endpoint, after the user as stopped typing.
    $field['delay'] = $field['delay'] ?? 500;
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <select
        name="{{ $field['name'] }}"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2FromAjaxElement"
        data-field-hide="{{ json_encode(isset($field['hide_when']) ? $field['hide_when'] : []) }}"
        data-field-show="{{ json_encode(isset($field['show_when']) ? $field['show_when'] : []) }}"    
        data-column-nullable="{{ var_export($field['allows_null']) }}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([]) }}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimum-input-length="{{ $field['minimum_input_length'] }}"
        data-data-source="{{ $field['data_source'] }}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-include-all-form-fields="{{ isset($field['include_all_form_fields']) ? ($field['include_all_form_fields'] ? 'true' : 'false') : 'false' }}"
        data-ajax-delay="{{ $field['delay'] }}"
        data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])
        >

        @if ($old_value)
            @php
                if(!is_object($old_value)) {
                    $item = $connected_entity->find($old_value);
                }else{
                    $item = $old_value;
                }

            @endphp
            @if ($item)
            {{-- allow clear --}}
            @if ($field['allows_null']))
            <option value="" selected>
                {{ $field['placeholder'] }}
            </option>
            @endif

            <option value="{{ $item->getKey() }}" selected>
                {{ $item->{$field['attribute']} }}
            </option>
            @endif
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <!-- include select2 css-->
    <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    {{-- allow clear --}}
    @if ($field['allows_null'])
    <style type="text/css">
        .select2-selection__clear::after {
            content: ' {{ trans('backpack::crud.clear') }}';
        }
    </style>
    @endif
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include select2 js-->
    <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
    @if (app()->getLocale() !== 'en')
    <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}"></script>
    @endif
    @endpush

@endif

<!-- include field specific select2 js-->
@push('crud_fields_scripts')
<script>
    function bpFieldInitSelect2FromAjaxElement(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $minimumInputLength = element.attr('data-minimum-input-length');
        var $dataSource = element.attr('data-data-source');
        var $method = element.attr('data-method');
        var $fieldAttribute = element.attr('data-field-attribute');
        var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
        var $allowClear = element.attr('data-column-nullable') == 'true' ? true : false;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));
        var $ajaxDelay = element.attr('data-ajax-delay');
        var $selectedOptions = typeof element.attr('data-selected-options') === 'string' ? JSON.parse(element.attr('data-selected-options')) : JSON.parse(null);

        var select2AjaxFetchSelectedEntry = function (element) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: $dataSource,
                    data: {
                        'keys': $selectedOptions
                    },
                    type: $method,
                    success: function (result) {

                        resolve(result);
                    },
                    error: function (result) {
                        reject(result);
                    }
                });
            });
        };

        // do not initialise select2s that have already been initialised
        if ($(element).hasClass("select2-hidden-accessible"))
        {
            return;
        }
        //init the element
        $(element).select2({
            theme: 'bootstrap',
            multiple: false,
            placeholder: $placeholder,
            minimumInputLength: $minimumInputLength,
            allowClear: $allowClear,
            ajax: {
                url: $dataSource,
                type: $method,
                dataType: 'json',
                delay: $ajaxDelay,
                data: function (params) {
                    if ($includeAllFormFields) {
                        return {
                            q: params.term, // search term
                            page: params.page, // pagination
                            form: form.serializeArray() // all other form inputs
                        };
                    } else {
                        return {
                            q: params.term, // search term
                            page: params.page, // pagination
                        };
                    }
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    var result = {
                        results: $.map(data.data, function (item, key) {
                            // textField = $fieldAttribute;
                            return {
                                text: item,
                                id: key
                            }
                        }),
                        pagination: {
                                more: data.current_page < data.last_page
                        }
                    };

                    return result;
                },
                cache: true
            },
        });

        // if we have selected options here we are on a repeatable field, we need to fetch the options with the keys
        // we have stored from the field and append those options in the select.
        if (typeof $selectedOptions !== typeof undefined &&
            $selectedOptions !== false &&
            $selectedOptions != '' &&
            $selectedOptions != null &&
            $selectedOptions != [])
        {
            var optionsForSelect = [];
            select2AjaxFetchSelectedEntry(element).then(result => {
                result.forEach(function(item, key) {
                    $itemText = item;
                    $itemValue = key;
                    //add current key to be selected later.
                    optionsForSelect.push($itemValue);

                    //create the option in the select
                    $(element).append('<option value="'+$itemValue+'">'+$itemText+'</option>');
                });

                // set the option keys as selected.
                $(element).val(optionsForSelect);
            });
        }

        // if any dependencies have been declared
        // when one of those dependencies changes value
        // reset the select2 value
        for (var i=0; i < $dependencies.length; i++) {
            var $dependency = $dependencies[i];
            //if element does not have a custom-selector attribute we use the name attribute
            if(typeof element.attr('data-custom-selector') == 'undefined') {
                form.find(`[name="${$dependency}"], [name="${$dependency}[]"]`).change(function(el) {
                        $(element.find('option:not([value=""])')).remove();
                        element.val(null).trigger("change");
                });
            }else{
                // we get the row number and custom selector from where element is called
                let rowNumber = element.attr('data-row-number');
                let selector = element.attr('data-custom-selector');

                // replace in the custom selector string the corresponding row and dependency name to match
                selector = selector
                    .replaceAll('%DEPENDENCY%', $dependency)
                    .replaceAll('%ROW%', rowNumber);

                $(selector).change(function (el) {
                    $(element.find('option:not([value=""])')).remove();
                    element.val(null).trigger("change");
                });
            }
        }
    }
</script>
<script>
    jQuery(document).ready(function($){

        window.$hiddenFields = window.$hiddenFields || {};
        window.$showedFields = window.$showedFields || {};

        var $hide = function( $radio ){

            $hideWhen = $radio.data('field-hide'),
            $value    = $radio.val(),
            $radioSet = $radio.attr('name');
            
            $hiddenFields[ $radioSet ] = $hiddenFields[ $radioSet ] || [];

            if( Object.keys($hiddenFields[ $radioSet ]).length ){
                $.each($hiddenFields[ $radioSet ], function(idx, field){
                    field.show();
                });
                $hiddenFields[ $radioSet ] = [];
            }

            if( typeof $hideWhen[ $value ] !== undefined ){
                $.each($hideWhen[ $value ], function(idx, name){

                    var f = $('[name="'+name+'"]').parents('.form-group');
                    
                    if( f.length ){
                        $hiddenFields[ $radioSet ].push(f);
                        f.hide();
                    }
                    else{
                        var f = $('[name="'+name+'[]"]').parents('.form-group');
                        
                        if( f.length ){
                            $hiddenFields[ $radioSet ].push(f);
                            f.hide();
                        }                                
                    }
                });
            }
        };
        
        var $show = function( $radio ){

            $showWhen = $radio.data('field-show'),
            $value    = $radio.val(),
            $radioSet = $radio.attr('name');
                        
            $showedFields[ $radioSet ] = $showedFields[ $radioSet ] || [];

            for (const [key, value] of Object.entries($showWhen)) {
                $.each($showWhen[ key ], function(idx, name){

                    var f = $('[name="'+name+'"]').parents('.form-group');

                    if( f.length ){
                        $showedFields[ $radioSet ].push(f);
                        f.hide();
                    }
                    else{
                        var f = $('[name="'+name+'[]"]').parents('.form-group');
                        
                        if( f.length ){
                            $showedFields[ $radioSet ].push(f);
                            f.hide();
                        }                                
                    }
                });
            }
            
            if( Object.keys($showedFields[ $radioSet ]).length ){
                $.each($showedFields[ $radioSet ], function(idx, field){
                    field.hide();
                });
                $showedFields[ $radioSet ] = [];
            }
            
            if( typeof $showWhen[ $value ] !== undefined ){
                $.each($showWhen[ $value ], function(idx, name){

                    var f = $('[name="'+name+'"]').parents('.form-group');

                    if( f.length ){
                        $showedFields[ $radioSet ].push(f);
                        f.show();
                    }
                    else{
                        var f = $('[name="'+name+'[]"]').parents('.form-group');
                        
                        if( f.length ){
                            $showedFields[ $radioSet ].push(f);
                            f.show();
                        }                                
                    }
                });
            }
            
        };

        $('select[data-field-show]').on('change', function(){
            return $show( $(this) );
        });

        $show( $('select[name={{ $field['name'] }}]') );
        
        $('select[data-field-hide]').on('change', function(){
            return $hide( $(this) );
        });

        $hide( $('select[name={{ $field['name'] }}]') );

        // $('select[data-field-toggle]:selected').each(function(){
        //     return $toggle( $(this) );
        // });
    });
</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
