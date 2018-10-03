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
                                    <input class="form-control" v-model="filterUserForm.group_id" @blur="getUsers">
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
                                        <option value="group_id">Group Name</option>
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
                                            <span v-if="isGroupManager(user) > -1">
                                                <button v-if="isGroupManager(user) == 0" class="btn btn-primary btn-sm" @click.prevent="modalMakeGroupMnanager(user)" data-toggle="tooltip" title="Make Group Manager"><i class="fa fa-check"></i></button>
                                                <button v-else class="btn btn-primary btn-sm" @click.prevent="modalDisableGroupMnanager(user)" data-toggle="tooltip" title="Disable Group Manager"><i class="fa fa-times"></i></button>
                                            </span>
                                            <span v-else>
                                                <button class="btn btn-secondary btn-sm" @click.prevent="modalMakeGroupMnanager(user)" data-toggle="tooltip" title="Make Group Manager" disabled><i class="fa fa-check"></i></button>
                                            </span>

                                            <span v-if="isAdministrator(user) > -1">
                                                <button v-if="isAdministrator(user) == 0" class="btn btn-success btn-sm" @click.prevent="modalMakeAdministrator(user)" data-toggle="tooltip" title="Make Administrator"><i class="fa fa-check"></i></button>
                                                <button v-else class="btn btn-success btn-sm" @click.prevent="modalDisableAdministrator(user)" data-toggle="tooltip" title="Disable Administrator"><i class="fa fa-times"></i></button> 
                                            </span>
                                            <span v-else>
                                                <button class="btn btn-secondary btn-sm" @click.prevent="modalMakeAdministrator(user)" data-toggle="tooltip" title="Make Administrator" disabled><i class="fa fa-check"></i></button>
                                            </span>

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

        <!-- Make Group Manager -->
        <div class="modal" id="modal-group-manager" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="groupManagerPermission">
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
                        <button type="button" class="btn btn-success" @click.prevent="makeGroupManager()">
                            Yes, Make
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Disable Group Manager -->
        <div class="modal" id="modal-disable-group-manager" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="groupManagerPermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Disable Group Manager Permission
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to disable this User as the group manager?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="disableGroupManager()">
                            Yes, Disable
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Make Administrator -->
        <div class="modal" id="modal-administrator" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="administratorPermission">
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
                        <button type="button" class="btn btn-success" @click.prevent="makeAdministrator()">
                            Yes, Make
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disable Administrator -->
        <div class="modal" id="modal-disable-administrator" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="administratorPermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Disable Administrator Permission
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to disable this User as the administrator?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="disableAdministrator()">
                            Yes, Disable
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
                roles: {},
                filterUserForm: {
                    sortBy : 'group_id',
                    order: 'desc',
                    group_id : '',
                    org_num : '',
                    contact_person : '',
                    phone_num : '',
                    email : '',
                    user_role : '',
                    pageLength: 5
                },
                deletingUser : 1,
                groupManagerPermission : 1,
                administratorPermission : 1,
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
                let group_id = '';
                if (typeof user.profile.group !== 'undefined') {
                    if (user.profile.group) {
                        group_id = user.profile.group.group_id
                    }
                }
                return group_id;
            },
            getUserRole(user){
                let user_role = '';
                if (typeof user.profile.role !== 'undefined') {
                    if (user.profile.role) {
                        for (var role_no = 0; role_no < user.profile.role.length; role_no++) {
                            if(user.profile.role[role_no].role_id == 1)
                                user_role = user_role + '<span class="label label-danger">Super</span>';
                            else if(user.profile.role[role_no].role_id == 2)
                                user_role = user_role + '<span class="label label-info">Admin</span>';
                            else if(user.profile.role[role_no].role_id == 3)
                                user_role = user_role + '<span class="label label-primary">Group</span>';
                            else if(user.profile.role[role_no].role_id == 4)
                                user_role = user_role + '<span class="label label-success">User</span>';
                        }
                    }
                }
                return user_role;
            },
            getUserStatus(user){
                if(user.status == 'pending')
                    return '<span class="label label-warning">Pending</span>';
                else if(user.status == 'activated')
                    return '<span class="label label-success">Activated</span>';
                else if(user.status == 'pending_activated')
                    return '<span class="label label-primary">Pending Activated</span>';
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
                    if (error.response.data.message) {
                        toastr['error'](error.response.data.message);
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
            },

            isGroupManager(user) {
                if (user.status == 'activated') {
                    if (user.profile.group_id) {
                        if (user.profile.role) {
                            for (var role_no = 0; role_no < user.profile.role.length; role_no++) {
                                if (user.profile.role[role_no].role_id == 3) {
                                    return 1;
                                } else if (user.profile.role[role_no].role_id == 4) {
                                    return 0;
                                }
                            }
                        }
                    }
                }
                return -1;
            },

            modalMakeGroupMnanager(user) {
                this.user_id = user.id;
                $('#modal-group-manager').modal('show');
            },
            makeGroupManager() {
                axios.post('/api/user/role?group=1&id=' + this.user_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-group-manager').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            },

            modalDisableGroupMnanager(user) {
                this.user_id = user.id;
                $('#modal-disable-group-manager').modal('show');
            },
            disableGroupManager() {
                axios.post('/api/user/role?group=0&id=' + this.user_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-disable-group-manager').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.message) {
                        toastr['error'](error.response.data.message);
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
            },
            
            isAdministrator(user) {
                if (user.status == 'activated') {
                    if (user.profile.role) {
                        for (var role_no = 0; role_no < user.profile.role.length; role_no++) {
                            if (user.profile.role[role_no].role_id <= 2) {
                                return 1;
                            }
                        }
                    }
                } else {
                    return -1;
                }
                return 0;
            },

            modalMakeAdministrator(user) {
                this.user_id = user.id;
                $('#modal-administrator').modal('show');
            },
            makeAdministrator() {
                axios.post('/api/user/role?admin=1&id=' + this.user_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-administrator').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.message) {
                        toastr['error'](error.response.data.message);
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
            },

            modalDisableAdministrator(user) {
                this.user_id = user.id;
                $('#modal-disable-administrator').modal('show');
            },
            disableAdministrator() {
                axios.post('/api/user/role?admin=0&id=' + this.user_id).then(response => {
                    toastr['success'](response.data.message);
                    $('modal-disable-administrator').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.message) {
                        toastr['error'](error.response.data.message);
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
            },
        },
        filters: {
        }
    }
</script>
