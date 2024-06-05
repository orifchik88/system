@extends('admin.layouts.main')
@section('content')
    @push('style')
        <style>
            .form-check-label {
                text-transform: capitalize;
            }
        </style>
    @endpush
    <div class="main-content-inner">
        <div class="row">
            <div class="col-lg-12 col-ml-12">
                <div class="row">
                    <!-- Textual inputs start -->
                    <div class="col-12 mt-5">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Role Create {{$role->name}}</h4>
                                @include('admin.layouts.messages')
                                <form action="{{route('admin.roles.store')}}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="example-text-input" class="col-form-label">Name</label>
                                        <input class="form-control" type="text" name="name" id="example-text-input" required="true" value="{{$role->name}}">
                                    </div>

                                    <div class="form-group">
                                        <label for="name">Permissions</label>
                                        <div class="form-check">
                                            <input type="checkbox" id="checkPermissionAll"  class="form-check-input"/>
                                            <label class="form-check-label" for="checkPermissionAll">All</label>
                                        </div>
                                        <hr>

                                        @php $i = 1; @endphp
                                        @foreach ($permissionsGroup as $group)
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="{{ $i }}Management" value="{{ $group->name }}"  onclick="checkPermissionByGroup('role-{{ $i }}-management-checkbox', this)">
                                                        <label class="form-check-label" for="checkPermission">{{ $group->name }}</label>
                                                    </div>
                                                </div>

                                                <div class="col-9 role-{{ $i }}-management-checkbox">
                                                    @php
                                                        $permissions = App\Models\User::getpermissionsByGroupName($group->name);
                                                        $j = 1;
                                                    @endphp
                                                    @foreach ($permissions as $permission)
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="permissions[]" id="checkPermission{{ $permission->id }}" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }} value="{{ $permission->name }}">
                                                            <label class="form-check-label" for="checkPermission{{ $permission->id }}">{{ $permission->name }}</label>
                                                        </div>
                                                        @php  $j++; @endphp
                                                    @endforeach
                                                    <br>
                                                </div>

                                            </div>
                                            @php  $i++; @endphp
                                        @endforeach
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4"><i class="fa fa-plus-circle"></i>  Create</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Textual inputs end -->
                    <!-- Radios start -->
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            $('#checkPermissionAll').click(function(){
                if($(this).is(':checked')){
                    $('input[type=checkbox]').prop('checked', true);
                }else{
                    $('input[type=checkbox]').prop('checked', false);
                }
            })

            function checkPermissionByGroup(className, checkThis){
                const groupIdName = $("#"+checkThis.id);
                const classCheckBox = $('.'+className+' input');

                if(groupIdName.is(':checked')){
                    classCheckBox.prop('checked', true);
                }else{
                    classCheckBox.prop('checked', false);
                }
                implementAllChecked();
            }
        </script>
    @endpush
@endsection

