<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">User</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item active">User</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter User</h4>
                        
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
                                    <label for="">Group Name</label>
                                    <select name="groups" class="form-control" v-model="filterUserForm.group_id" @change="getUsers">
                                        <option value="0">All</option>
                                        <option v-for="group in groups.groups" v-bind:value="group.id">{{group.name}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Country</label>
                                    <select name="country" class="form-control" v-model="filterUserForm.country" @change="getUsers">
                                        <option value="0">All</option>
                                        <option v-for="country in countries.countries" v-bind:value="country">{{country}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">User Role</label>
                                    <select name="roles" class="form-control" v-model="filterUserForm.admin" @change="getUsers">
                                        <option value="-1">All</option>
                                        <option value="1">Group Manager</option>
                                        <option value="0">User</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterUserForm.sortBy" @change="getUsers">
                                        <option value="created_at">Created At</option>
                                        <option value="full_name">Full Name</option>
                                        <option value="email">Email</option>
                                        <option value="country">Country</option>
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
                                        <th>User</th>
                                        <th>User Group</th>
                                        <th>Street Address</th>
                                        <th>Zip Code</th>
                                        <th>Country</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users.data">
                                        <td class="user-profile" v-html="getUser(user)"></td>
                                        <td v-html="getUserGroup(user)"></td>
                                        <td v-text="getUserStreetAddress(user)"></td>
                                        <td v-text="getUserZipCode(user)"></td>
                                        <td v-text="getUserCountry(user)"></td>
                                        <td v-text="user.created_at"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="viewUserProfile(user)" data-toggle="tooltip" title="View User Profile"><i class="fa fa-eye"></i></button>
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
                countries: {},
                filterUserForm: {
                    sortBy : 'created_at',
                    order: 'desc',
                    full_name : '',
                    email : '',
                    group_id : 0,
                    country : 0,
                    admin : -1,
                    user_role : 0,
                    pageLength: 100
                },
                deletingUser : 1,
                user_id: 0
            }
        },
        mounted() {
            this.getCountries();
            this.getGroups();
            this.getUsers();
        },
        methods: {
            getCountries() {
                axios.get('/admin/countries').then(response => {
                    this.countries = response.data;
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
            getGroups() {
                axios.get('/admin/groups').then(response => {
                    this.groups = response.data;
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
                axios.get('/admin/user?&page=' + page + url).then(response => {
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
            getUserGroup(user){
                var userGroupHtml = "";
                if (user.groups) {
                    for (var index = 0; index < user.groups.length; index++) {
                        if (user.groups[index].status == 'activated') {
                            if (user.groups[index].admin == '1') {
                                userGroupHtml = userGroupHtml + '<p class="label label-primary" style="display: block!important">';
                            } else {
                                userGroupHtml = userGroupHtml + '<p class="label label-success" style="display: block!important">';
                            }
                        } else {
                            if (user.groups[index].admin == '1') {
                                userGroupHtml = userGroupHtml + '<p class="label label-light-primary" style="display: block!important">';
                            } else {
                                userGroupHtml = userGroupHtml + '<p class="label label-light-success" style="display: block!important">';
                            }
                        }
                        if (user.groups[index].group) {
                            userGroupHtml = userGroupHtml + user.groups[index].group.name;
                        }
                        userGroupHtml = userGroupHtml + '</p>'
                    }
                }
                return userGroupHtml;
            },
            getUserStreetAddress(user) {
                if(user.profile)
                    return user.profile.street_address;
                    
                return '';
            },
            getUserZipCode(user) {
                if(user.profile)
                    return user.profile.postal_code;
                    
                return '';
            },
            getUserCountry(user) {
                if(user.profile)
                    return user.profile.country;
                    
                return '';
            },
            getUserStatus(user){
                if(user.status == 'pending')
                    return '<span class="label label-warning">Pending</span>';
                else if(user.status == 'activated')
                    return '<span class="label label-success">Activated</span>';
                else if(user.status == 'banned')
                    return '<span class="label label-danger">Banned</span>';
                else
                    return;
            },
            viewUserProfile(user) {
                this.$router.push('/user/'+user.id+'/view');
            },

            modalDeleteUser(user) {
                this.user_id = user.id;
                $('#modal-delete-user').modal('show');
            },
            deleteUser() {
                axios.delete('/admin/user/' + this.user_id).then(response => {
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
        },
        filters: {
        }
    }
</script>
