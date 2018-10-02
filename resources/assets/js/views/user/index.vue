<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">User</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">Home</router-link></li>
                    <li class="breadcrumb-item active">New User</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter User</h4>
                        
                        <div class="row m-t-40">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Contact Person</label>
                                    <input class="form-control" v-model="filterUserForm.contact_person" @blur="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Group ID</label>
                                    <input class="form-control" v-model="filterUserForm.group_name" @blur="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Phone Number</label>
                                    <input class="form-control" v-model="filterUserForm.phone_number" @blur="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input class="form-control" v-model="filterUserForm.email" @blur="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">User Role</label>
                                    <input class="form-control" v-model="filterUserForm.user_role" @blur="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterUserForm.sortBy" @change="getUsers">
                                        <option value="contact_person">Contact Person</option>
                                        <option value="group_name">Group Name</option>
                                        <option value="phone_number">Phone Number</option>
                                        <option value="email">Email</option>
                                        <option value="user_role">User Role</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterUserForm.order" @change="getUsers">
                                        <option value="asc">Asc</option>
                                        <option value="desc">Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="card-title">User List</h4>
                        <h6 class="card-subtitle" v-if="users.total">Total {{users.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="users.total">
                                <thead>
                                    <tr>
                                        <th>Contact Person</th>
                                        <th>Group ID</th>
                                        <th>Phone Number</th>
                                        <th>Email</th>
                                        <th>User Role</th>
                                        <th>Status</th>
                                        <th style="width:150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users.data">
                                        <td v-text="user.profile.contact_person"></td>
                                        <td v-html="getUserGroupID(user)"></td>
                                        <td v-text="user.profile.phone_number"></td>
                                        <td v-text="user.email"></td>
                                        <td v-html="getUserRole(user)"></td>
                                        <td v-html="getUserStatus(user)"></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalGroupMnanager(user)" data-toggle="tooltip" title="Assign Group Manager"><i class="fa fa-trash"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalAdministrator(user)" data-toggle="tooltip" title="Assign Admin Permission"><i class="fa fa-trash"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteUser(user)" data-toggle="tooltip" title="Delete User"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="users" :limit=3 v-on:pagination-change-page="getUsers"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterUserForm.pageLength" @change="getUsers" v-if="users.total">
                                            <option value="5">5 per page</option>
                                            <option value="10">10 per page</option>
                                            <option value="25">25 per page</option>
                                            <option value="100">100 per page</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete User Modal -->
        <div class="modal" id="modal-delete-user" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingUser">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete User
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this User?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteUser()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete User Modal -->
        <div class="modal" id="modal-group-manager" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingUser">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Make Group Manager Permission
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to make this User as the group manager?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="makeGroupManager()">
                            Yes, Make
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete User Modal -->
        <div class="modal" id="modal-administrator" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingUser">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Make Administrator Permission
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to make this User as the administrator?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="makeAdmin()">
                            Yes, Make
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { pagination, ClickConfirm },
        data() {
            return {
                users: {},
                groups: {},
                filterUserForm: {
                    sortBy : 'group_name',
                    order: 'desc',
                    group_name : '',
                    org_num : '',
                    contact_person : '',
                    phone_num : '',
                    email : '',
                    user_role : '',
                    pageLength: 5
                },
                group_id : 0,
                deletingUser : 1,
                user_id: 0
            }
        },
        mounted() {
            this.getUsers();
        },
        methods: {
            getUsers(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterUserForm);
                axios.get('/api/user?&page=' + page + url)
                    .then(response => this.users = response.data );
            },
            getUserGroupID(user) {
                let group_name = '';
                if (typeof user.profile.group !== 'undefined') {
                    if (user.profile.group) {
                        group_name = user.profile.group.name
                    }
                }
                return group_name;
            },
            getUserRole(user){
                let user_role = '';
                if (typeof user.profile.role !== 'undefined') {
                    if (user.profile.role) {
                        user_role = user.profile.role.name;
                    }
                }
                return user_role;
            },
            getUserStatus(user){
                if(user.status == 'pending')
                    return '<span class="label label-warning">Pending</span>';
                else if(user.status == 'activated')
                    return '<span class="label label-success">Activated</span>';
                else if(user.status == 'assigned')
                    return '<span class="label label-info">Assigned</span>';
                else if(user.status == 'banned')
                    return '<span class="label label-danger">Banned</span>';
                else
                    return;
            },
            modalDeleteUser(user) {
                this.user_id = user.id;
                $('#modal-delete-user').modal('show');
            },
            deleteUser() {
                axios.delete('/api/user/' + this.user_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-delete-user').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            },
            modalGroupMnanager(user) {
                this.user_id = user.id;
                $('#modal-group-manager').modal('show');
            },
            makeGroupManager() {
                axios.delete('/api/user/' + this.user_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-group-manager').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            },
            modalAdministrator(user) {
                this.user_id = user.id;
                $('#modal-administrator').modal('show');
            },
            makeAdmin() {
                axios.delete('/api/user/' + this.user_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-administrator').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            },
        },
        filters: {
        }
    }
</script>
