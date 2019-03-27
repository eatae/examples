<?php
/*
 * @var  $vacancies  Illuminate\Database\Eloquent\Collection  [App\Models\HumanResource\Vacancy]
 * @var  $counters  array
 */
?>

@extends('layouts.app')

@include('inc.styles')

@section('content')

<div class="ui container" style="margin-top: 20px">
    <div id='main_block'>

        {{-- top-block --}}
        <div class="ui grid">

            <div class="three wide column center aligned">
                {{--<h1 class="title">--}}
                    {{--Vacancy--}}
                {{--</h1>--}}
            </div>

            <div class="thirteen wide column right aligned">
                <a href="{{ route('human-resource.vacancy.create') }}" class="ui yellow button"><i class="plus icon"></i>Add</a>
            </div>

        </div>


        {{-- content block --}}
        <div class="ui grid">

            <!-- left menu -->
            <div class="three wide column">
                @include('inc.hr-left-menu')
            </div>

            <!-- content -->
            <div class="thirteen wide column">

                <table class="ui celled striped table">
                    <thead>
                    <tr>
                        <th class="center aligned">Role</th>
                        <th class="center aligned">Priority</th>
                        <th class="center aligned">Status</th>
                        <th class="center aligned">Structure</th>
                        <th class="center aligned">Created</th>
                        <th class="center aligned">Salary</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($vacancies as $vacancy)
                        <tr>
                            <!-- role -->
                            <td class="center aligned bold">
                                {{ $vacancy->role->name }}
                            </td>
                            <!-- priority -->
                            <td class="center aligned">
                                {{ $vacancy->priority }}
                            </td>
                            <!-- status -->
                            <td class="center aligned">
                                {{ $vacancy->status }}
                            </td>
                            <!-- structure -->
                            <td class="center aligned">
                                {{ $vacancy->structure->company->title }}<br>
                                {{ $vacancy->structure->office->title }}<br>
                                {{ $vacancy->structure->department->title }}
                            </td>
                            <!-- Created -->
                            <td class="center aligned">
                                <div>
                                    {{ $vacancy->creator_user->full_name() ?? 'not exists' }}
                                </div>
                                <div>
                                    {{ $vacancy->created_at->format('Y-m-d') }}
                                </div>
                            </td>
                            <!-- min / max salary -->
                            <td class="center aligned">
                                {{ $vacancy->min_salary }}/{{ $vacancy->max_salary }}
                            </td>

                            <!-- buttons -->
                            <td class="center aligned right_buttons">

                                <!-- Read -->
                                <a href="{{ route('human-resource.vacancy.show-one', $vacancy->id)}}" class="orange ui icon button" data-tooltip="Read" data-position="top center">
                                  <i class="file outline icon"></i>
                                </a>

                                <!-- Edit -->
                                <a href="{{ route('human-resource.vacancy.edit', $vacancy->id)}}" class="orange ui icon button" data-tooltip="Edit" data-position="top center">
                                    <i class="edit icon"></i>
                                </a>
                            </td>
                            <!-- buttons -->
                            <td class="center aligned right_buttons">

                                <!-- Human resource -->
                                <a href="{{ route('human-resource.vacancy.show-hrs', $vacancy->id ) }}" class="brown ui icon button" data-tooltip="Human resource" data-position="top center">
                                    <i class="user outline icon"></i>
                                </a>

                                <!-- Candidates -->
                                <a href="{{ route('human-resource.vacancy.show-candidates', $vacancy->id ) }}" class="brown ui icon button" data-tooltip="Candidates" data-position="top center">
                                    <i class="address card outline icon"></i>
                                </a>

                                <div class="right_buttons__outside">
                                    <!-- Close icon -->
                                    @php( $modal_element_id = 'modal_with_comment_'.$vacancy->id)

                                    <a href="javascript:void(0);"
                                       onclick="runModalFormWithCommentPut('{{ $modal_element_id }}')"
                                       data-tooltip="Close"
                                       data-position="top center"
                                       class="main-trash-btn main-margin-bottom main-close-btn--vacancy">
                                        <i class="file excel outline icon"></i>
                                    </a>
                                    {{-- modal close --}}
                                    @include('code_block.modal-form-with-comment-put', [
                                        'header' => 'Close',
                                        'message' => 'Are you sure you want to close the vacancy?',
                                        'route' => route('human-resource.vacancy.close'),
                                        'modal_element_id' => $modal_element_id,
                                        'params' => ['vacancy_id' => $vacancy->id]
                                    ])

                                    <!-- Delete icon -->
                                    <a href="javascript:void(0);" data-tooltip="Delete" data-position="top center"
                                       onclick="runBasicModalPut(
                                               '{{ route('human-resource.vacancy.delete') }}',
                                               '{{ $vacancy->title }}',
                                               'Do you really want to delete this vacancy?',
                                               '{{ csrf_token() }}',
                                               {'vacancy_id':'{{ $vacancy->id }}'}
                                               )"
                                       class="main-trash-btn main-trash-btn--vacancy">
                                        <i class="trash alternate outline icon"></i>
                                    </a>
                                </div>

                            </td>
                        </tr>

                    @endforeach
                    </tbody>

                    <tfoot>
                    <tr>

                        <!-- pagination -->
                        {{--<th colspan="8">--}}
                        {{--<div class="ui right floated pagination menu">--}}
                        {{--<a class="icon item">--}}
                        {{--<i class="left chevron icon"></i>--}}
                        {{--</a>--}}
                        {{--<a class="item">1</a>--}}
                        {{--<a class="item">2</a>--}}
                        {{--<a class="item">3</a>--}}
                        {{--<a class="item">4</a>--}}
                        {{--<a class="icon item">--}}
                        {{--<i class="right chevron icon"></i>--}}
                        {{--</a>--}}
                        {{--</div>--}}
                        {{--</th>--}}

                    </tr>
                    </tfoot>
                </table>
            </div>


        </div>

    </div>
</div>

@endsection

@include('inc.scripts')
