<?php
/**
 * @var  $priorities  array
 * @var  $statuses  array
 * @var  $user  App\Models\User
 * @var  $roles  \Illuminate\Database\Eloquent\Collection  [App\Models\Structure\Role]
 * @var  $structures  array
 */
?>

@extends('layouts.app')

@include('inc.styles')

@section('content')

    <div id="vacancy-show-edit-wrapper">

        <!-- text container -->
        <div class="ui text container">

            <!-- head -->
            <div class="ui column">
                <h1 class="ui header centered">
                    Module
                </h1>
            </div>


            <div class="ui column">
                <!-- segment -->
                <div class="ui raised segment">

                    <form class="ui form">
                        <h4 class="ui dividing header">Shipping Information</h4>

                        <div class="field">
                            <label>Name</label>
                            <div class="two fields">
                                <div class="field">
                                    <input type="text" name="shipping[first-name]" placeholder="First Name">
                                </div>
                                <div class="field">
                                    <input type="text" name="shipping[last-name]" placeholder="Last Name">
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label>Billing Address</label>
                            <div class="fields">
                                <div class="twelve wide field">
                                    <input type="text" name="shipping[address]" placeholder="Street Address">
                                </div>
                                <div class="four wide field">
                                    <input type="text" name="shipping[address-2]" placeholder="Apt #">
                                </div>
                            </div>
                        </div>

                        <!-- module -->
                        <div class="field">
                            <div id="dynamic-options" class="three fields" data-json="{{ $structures }}">

                                <div class="field">
                                    <label>Company</label>
                                    <select class="ui fluid dropdown dynamic-options" name="company_id" required="true">
                                        <option value="" hidden>Select your option</option>
                                        <option value="1">KeyG</option>
                                        <option value="2">Transport</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label>Office</label>
                                    <select class="ui fluid dropdown dynamic-options" name="offices_id" disabled>
                                        <option value="" hidden>Select your option</option>
                                    </select>
                                </div>

                                <div class="field">

                                    <label>Department</label>
                                    <select class="ui fluid dropdown dynamic-options" name="department_id" disabled>
                                        <option value="" hidden>Select your option</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- end module -->


                    </form>

                </div>
                <!-- end segment -->
            </div>

        </div>

    </div>

    <script>
        /* вынести в main */
        $('.ui.dropdown').dropdown();

        $( function(){
            let block = $('div#dynamic-options');
            let selects = block.find('select');
            let data = block.data('json');



            selects.on('change', function(event) {

                let self = $(this);
                let parent = self.parents('div#dynamic-options');
                let selects = parent.find('select');


                let flag = 0;
                let values = [];
                let options = null;
                let nextSelect;

                /* Each */
                selects.each( function (idx, select_el) {

                    select_el = $(select_el);
                    let nextSelect = select_el.parent().parent('.field').find('select');

                    if (flag == 0) {
                        values.push(select_el.val());
                    }

                    else if (flag == 1 && select_el.attr('name') == nextSelect.attr('name')) {
                        flag = 2;
                        console.log('foo');
                    }

                    else if (flag = 2) {

                    }

                    if (flag == 0 && select_el.attr('name') == self.attr('name')) {
                        /* вставляем options  */
                        nextSelect = select_el.parent().parent('.field').next('div').find('select');
                        let nextSelectParent = nextSelect.parent('div');
                        let nextText = nextSelectParent.find('div.text');

                        let searched = recursionSearchData(values, data);
                        let options = createOptionElements(searched);

                        nextSelect.empty();
                        nextSelect.append(options);
                        nextSelectParent.removeClass('disabled');
                        nextSelect.prop('disabled', false);

                        nextText.text('Select your option');
                        flag = 1;
                    }

                });


                /**
                 *
                 * @param values array  ['1', '5', '14']
                 * @param data   json   nested items
                 */
                function recursionSearchData(values, data) {
                    let result;
                    let key = values.shift();
                    let necessary_prop = data[key];

                    for (let item in necessary_prop) {
                        if (typeof necessary_prop[item] == 'object') {
                            result = necessary_prop[item];
                        }
                    }
                    if (values.length == 0) {
                        return result;
                    }
                    return recursionSearchData(values, result);
                }


                /**
                 *
                 * @param searched  array  searched array
                 */
                function createOptionElements(searched) {
                    let options = $("<option value='' hidden>Select your option</option>");
                    /* add elements */
                    for (let key in searched) {
                        let element = $("<option value=" + searched[key]['id'] + ">" + searched[key]['title'] + "</option>");
                        options = options.add(element);
                    }
                    return options;
                }

            });
            /* end event */

        });

    </script>

@endsection

@include('inc.scripts')
