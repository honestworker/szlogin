<template>
    <div class="m-t-20">
        <div class="row">
            <div class="form-group col-md-4 col-lg-4 col-sm-12">
                <div class="row">
                    <div class="col-md-5 col-lg-5 col-sm-12">
                        <strong><label for="name">Group Name:</label></strong>
                    </div>
                    <div class="col-md-7 col-lg-7 col-sm-12">
                        <span v-text="name"></span>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-4 col-lg-4 col-sm-12">
                <div class="row">
                    <div class="col-md-5 col-lg-5 col-sm-12">
                        <strong><label for="postal_code">Postal Code:</label></strong>
                    </div>
                    <div class="col-md-7 col-lg-7 col-sm-12">
                        <span v-text="postal_code"></span>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-4 col-lg-4 col-sm-12">
                <div class="row">
                    <div class="col-md-5 col-lg-5 col-sm-12">
                        <strong><label for="country">Country:</label></strong>
                    </div>
                    <div class="col-md-7 col-lg-7 col-sm-12">
                        <span v-text="country"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Group User</h4>
                        <div class="row m-t-20">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Full Name</label>
                                    <input class="form-control" v-model="filterUserForm.full_name" @change="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input class="form-control" v-model="filterUserForm.email" @change="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Street Address</label>
                                    <input class="form-control" v-model="filterUserForm.street_address" @change="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Postal Code</label>
                                    <input class="form-control" v-model="filterUserForm.postal_code" @change="getUsers">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterUserForm.status" @change="getUsers">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterUserForm.sortBy" @change="getUsers">
                                        <option value="full_name">Full Name</option>
                                        <option value="email">Email</option>
                                        <option value="street_address">Street Address</option>
                                        <option value="postal_code">Postal Code</option>
                                        <option value="created_at" selected>Created At</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterUserForm.order" @change="getUsers">
                                        <option value="asc">Asc</option>
                                        <option value="desc" selected>Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Group User List</h4>
                        <h6 class="card-subtitle" v-if="users.total">Total {{users.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="users.total">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Street Address</th>
                                        <th>Zip Code</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users.data">
                                        <td class="user-profile" v-html="getUser(user)"></td>
                                        <td v-text="getUserStreetAddress(user)"></td>
                                        <td v-text="getUserPostalCode(user)"></td>
                                        <td v-html="getUserRole(user)"></td>
                                        <td v-html="getUserStatus(user)"></td>
                                        <td v-text="getUserCreatedAt(user)"></td>
                                        <td>                                           
                                            <button v-if="isGroupActivate(user) == 0" class="btn btn-primary btn-sm" @click.prevent="modalActiveGroupMember(user)" data-toggle="tooltip" title="Active Group Member"><i class="fa fa-user"></i></button>
                                            <button v-else class="btn btn-secondary btn-sm" @click.prevent="modalDeactiveGroupMember(user)" data-toggle="tooltip" title="Deactive Group Member"><i class="fa fa-gear"></i></button> 

                                            <button v-if="isGroupManager(user) == 0" class="btn btn-success btn-sm" @click.prevent="modalMakeGroupManager(user)" data-toggle="tooltip" title="Make Group Manager"><i class="fa fa-check"></i></button>
                                            <button v-else class="btn btn-secondary btn-sm" @click.prevent="modalDisableGroupManager(user)" data-toggle="tooltip" title="Disable Group Manager"><i class="fa fa-times"></i></button> 
                                            
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteUser(user)" data-toggle="tooltip" title="Delete Notification"><i class="fa fa-trash"></i></button>
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
        
        <!-- Active Group Memeber -->
        <div class="modal" id="modal-active-group" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="groupActivePermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Active Group Memeber
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to active this User as the group member?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-success" @click.prevent="activeGroupMember()">
                            Yes, Active
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deactive Group Memeber -->
        <div class="modal" id="modal-deactive-group" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="groupActivePermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Deactive Group Memeber
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to deactive this User as the group member?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deactiveGroupMember()">
                            Yes, Deactive
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Make Group Manager -->
        <div class="modal" id="modal-make-group-manager" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="groupManagerPermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Make Group Manager
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to make this User as the group mamanger?
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
                            Disable Group Manager
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to disable this User as the group mamanger?
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
                        Are you sure you want to delete this User from this Group?
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
    </div>
</template>

<script>
    import pagination from 'laravel-vue-pagination'
    import ClickConfirm from 'click-confirm'
    import helper from '../../services/helper'
    
    export default {
        components : { pagination, ClickConfirm },
        data() {
            return {
                name: '',
                postal_code: '',
                country: '',

                users: {},

                filterUserForm: {
                    group_id: this.id,
                    sortBy : 'created_at',
                    order: 'desc',
                    email : '',
                    full_name: '',
                    street_address : '',
                    postal_code : '',
                    status: '',
                    created_at: '',
                    pageLength: 100
                },
                
                deletingUser: 1,
                baseUrl : '',

                user_id: 0,
                usergroup_id: 0,

                groupActivePermission : 1,
                groupManagerPermission : 1,
            }
        },
        created() {
            this.getGroup();
            this.getUsers();
        },
        props: ['id'],
        mounted() {
        },

        methods: {
            proceed() {
            },
            getGroup() {
                axios.get('/admin/group/' + this.id)
                .then(response => {
                    this.name = response.data.name;
                    this.postal_code = response.data.postal_code;
                    this.country = response.data.country;
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            getUsers(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterUserForm);
                axios.get('/admin/usergroup/user?page=' + page + url).then(response => {
                    this.users = response.data;                    
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },

            getUser(user) {
                var userHtml = "";
                if (typeof user != 'undefined' && user !== null && user !== '') {
                    userHtml = "<div class='profile-img'>";
                    if (typeof user.profile != 'undefined' && user.profile !== null && user.profile !== '') {
                        if (user.profile.avatar) {
                            userHtml = userHtml + "<img src='/images/users/" + user.profile.avatar + "' alt='user'>";
                        } else {
                            userHtml = userHtml + "<img src='/images/common/no-user.png' alt='user'>";
                        }
                        userHtml = userHtml + "</div>";
                        userHtml = userHtml + "<p style='margin-bottom: 0px'>" + user.profile.full_name + "</p>";
                        userHtml = userHtml + "<p style='margin-bottom: 0px'>" + user.email + "</p>";
                    } else {
                        userHtml = userHtml + "<img src='/images/common/no-user.png' alt='user'></div>";
                    }
                }
                return userHtml;
            },
            getUserStreetAddress(user) {
                if (user.profile) {
                    return user.profile.street_address;
                }
                return '';
            },
            getUserPostalCode(user) {
                if (user.profile) {
                    return user.profile.postal_code;
                }
                return '';
            },
            getUserCreatedAt(user) {
                if (user.group) {
                    return user.group.created_at;
                }
                return '';
            },
            getUserRole(user) {
                if (user.group) {
                    if (user.group.admin == "1") {
                        return '<span class="label label-primary">Manager</span>';
                    } else {
                        return '<span class="label label-success">User</span>';
                    }
                }
                return '';
            },
            getUserStatus(user) {
                if (user.group) {
                    if (user.group.status == 'pending') {
                        return '<span class="label label-warning">Pending</span>';
                    } else if (user.group.status == 'activated') {
                        return '<span class="label label-success">Activated</span>';
                    }
                }
                return '';
            },
            isGroupActivate(user) {
                if (user) {
                    if (user.group) {
                        if (user.group.status == 'activated') {
                            return 1;
                        }
                    }
                }
                return 0;
            },
            isGroupManager(user) {
                if (user) {
                    if (user.group) {
                        if (user.group.admin == '1') {
                            return 1;
                        }
                    }
                }
                return 0;
            },
            
            modalDeleteUser(user) {
                this.user_id = user.id;
                this.usergroup_id = user.group.user_group;
                $('#modal-delete-user').modal('show');
            },
            deleteUser() {
                axios.delete('/admin/usergroup/' + this.user_id + '/' + this.usergroup_id).then(response => {
                    $('#modal-delete-user').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-delete-user').modal('hide');
                });
            },

            modalActiveGroupMember(user) {
                this.user_id = user.id;
                this.usergroup_id = user.group.user_group;
                $('#modal-active-group').modal('show');
            },
            activeGroupMember() {
                axios.post('/admin/usergroup/active/' + this.user_id + '/' + this.usergroup_id).then(response => {
                    $('#modal-active-group').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-active-group').modal('hide');
                });
            },
            
            modalDeactiveGroupMember(user) {
                this.user_id = user.id;
                this.usergroup_id = user.group.user_group;
                $('#modal-deactive-group').modal('show');
            },
            deactiveGroupMember() {
                axios.post('/admin/usergroup/deactive/' + this.user_id + '/' + this.usergroup_id).then(response => {
                    $('#modal-deactive-group').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-deactive-group').modal('hide');
                });
            },

            modalMakeGroupManager(user) {
                this.user_id = user.id;
                this.usergroup_id = user.group.user_group;
                $('#modal-make-group-manager').modal('show');
            },
            makeGroupManager() {
                axios.post('/admin/usergroup/manager/' + this.user_id + '/' + this.usergroup_id).then(response => {
                    $('#modal-make-group-manager').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-make-group-manager').modal('hide');
                });
            },

            modalDisableGroupManager(user) {
                this.user_id = user.id;
                this.usergroup_id = user.group.user_group;
                $('#modal-disable-group-manager').modal('show');
            },
            disableGroupManager() {
                axios.post('/admin/usergroup/user/' + this.user_id + '/' + this.usergroup_id).then(response => {
                    $('#modal-disable-group-manager').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-disable-group-manager').modal('hide');
                });
            },

            start() {
                console.log('Starting File Management Component');
            },
        },
        computed: {
            
        }
    }
</script>