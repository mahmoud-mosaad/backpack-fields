<!-- select -->
@php
    $current_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';
    $entity_model = $crud->getRelationModel($field['entity'],  - 1);

    //if it's part of a relationship here we have the full related model, we want the key.
    if (is_object($current_value) && is_subclass_of(get_class($current_value), 'Illuminate\Database\Eloquent\Model') ) {
        $current_value = $current_value->getKey();
    }

    if (!isset($field['options'])) {
        $options = $field['model']::all();
    } else {
        $options = call_user_func($field['options'], $field['model']::query());
    }
@endphp

@include('crud::fields.inc.wrapper_start')

<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

<select
    name="{{ $field['name'] }}"
    data-field-hide="{{ json_encode(isset($field['hide_when']) ? $field['hide_when'] : []) }}"
    data-field-show="{{ json_encode(isset($field['show_when']) ? $field['show_when'] : []) }}"
    @include('crud::fields.inc.attributes')
>

    @if ($entity_model::isColumnNullable($field['name']))
        <option value="">-</option>
    @endif

    @if (count($options))
        @foreach ($options as $connected_entity_entry)
            @if($current_value == $connected_entity_entry->getKey())
                <option value="{{ $connected_entity_entry->getKey() }}" selected>{{ $connected_entity_entry->{$field['attribute']} }}</option>
            @else
                <option value="{{ $connected_entity_entry->getKey() }}">{{ $connected_entity_entry->{$field['attribute']} }}</option>
            @endif
        @endforeach
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
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')

    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
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

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
