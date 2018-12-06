<?php
/*
 * @var $structures JSON array (  json_encode( Structure::buildArrayForSelects() )  )
 *
 * include into <form>
 */
?>

<!-- Structure dynamic options -->
<div class="field">
    <div class="three fields" id="dynamic-options" data-json="{{ $structures }}">

        <div class="field">
            <label>Company</label>
            <select class="ui fluid dropdown dynamic-options" name="company_id">
                <option value="" hidden>Select your option</option>
                @foreach( json_decode($structures) as $structure )
                    <option value="{{ $structure->id }}">{{ $structure->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label>Office</label>
            <select class="ui fluid dropdown dynamic-options" name="office_id" disabled>
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
<!-- end structure -->


<script>

    /**
     * Structure ( dynamic select options )
     * ------------------------------------
     *
     */
    ( function() {

        let block = $('div#dynamic-options');
        let selects = block.find('select');
        let data = block.data('json');


        /* change select */
        selects.on('change', function(event) {

            let self = $(this);
            let parent = self.parents('div#dynamic-options');
            let selects = parent.find('select');

            let flag = 0;
            let values = [];
            let options = null;

            /* Each */
            selects.each( function (idx, select_el) {

                select_el = $(select_el);
                let nextSelect = select_el.parent().parent('.field').find('select');

                if (flag == 0) {
                    values.push(select_el.val());
                }

                if (flag == 0 && select_el.attr('name') == self.attr('name')) {
                    /* append options  */
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

        });


        /**
         * Search data (recursion)
         *
         * @param values array  ['1', '5', '14']
         * @param data   json   nested items
         */
        function recursionSearchData(values, data) {
            let key = values.shift();
            let necessary_prop = data[key];
            let result;

            for (let item in necessary_prop) {
                if (typeof necessary_prop[item] == 'object') {
                    result = necessary_prop[item];
                }
            }
            if (values.length == 0) {
                // результат в виде объекта со списком елементов
                return result;
            }
            // вызываем рекурсивно эту же функцию, которая вернёт result.
            return recursionSearchData(values, result);
        }



        /**
         * Create options element
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

    }());

</script>

