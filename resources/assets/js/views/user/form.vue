<template>
    <form @submit.prevent="proceed">
        <div class="view-user-profile">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <strong><p>Avatar: </p></strong>
                                <div class="profile-img"> <img :src="getAvatar" alt="user" /></div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-4 text-right">
                                        <strong><span>Full Name: </span></strong>
                                    </div>
                                    <div class="col-lg-8">
                                        <input class="form-control" type="text" value="userForm.full_name" v-model="userForm.full_name">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-4 text-right">
                                        <strong><span>Email: </span></strong>
                                    </div>
                                    <div class="col-lg-8">
                                        <input class="form-control" type="text" value="userForm.email" v-model="userForm.email">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-4 text-right">
                                        <strong><span>Street Address: </span></strong>
                                    </div>
                                    <div class="col-lg-8">
                                        <input class="form-control" type="text" value="userForm.street_address" v-model="userForm.street_address">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-4 text-right">
                                        <strong><span>Postal Code: </span></strong>
                                    </div>
                                    <div class="col-lg-8">
                                        <input class="form-control" type="text" value="userForm.postal_code" v-model="userForm.postal_code">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-4 text-right">
                                        <strong><span>Country: </span></strong>
                                    </div>
                                    <div class="col-lg-8">                                    
                                        <select name="country" class="form-control" v-model="userForm.country">
                                            <option v-for="country in countries.countries" v-bind:value="country" v-bind:selected="getSelectedCountryStatus(country)">{{country}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-4 text-right">
                                        <strong><span>Created At: </span></strong>
                                    </div>
                                    <div class="col-lg-8">
                                        <span v-text="userForm.created_at"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <router-link to="/user" class="btn btn-danger waves-effect waves-light m-t-10" v-show="id">Cancel</router-link>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Filter User Group</h4>
                            
                            <div class="row m-t-20">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Group Name</label>
                                        <input class="form-control" v-model="filterUserGroupForm.name" @change="getUserGroups">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Postal Code</label>
                                        <input class="form-control" v-model="filterUserGroupForm.postal_code" @change="getUserGroups">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">User Role</label>
                                        <select name="roles" class="form-control" v-model="filterUserGroupForm.admin" @change="getUserGroups">
                                            <option value="-1">All</option>
                                            <option value="1">Group Manager</option>
                                            <option value="0">User</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Status</label>
                                        <select name="roles" class="form-control" v-model="filterUserGroupForm.status" @change="getUserGroups">
                                            <option value="">All</option>
                                            <option value="activated">Activated</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Sort By</label>
                                        <select name="sortBy" class="form-control" v-model="filterUserGroupForm.sortBy" @change="getUserGroups">
                                            <option value="created_at">Created At</option>
                                            <option value="name">Name</option>
                                            <option value="postal_code">Postal Code</option>
                                            <option value="role">Role</option>
                                            <option value="role">Status</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Order</label>
                                        <select name="order" class="form-control" v-model="filterUserGroupForm.order" @change="getUserGroups">
                                            <option value="asc">Asc</option>
                                            <option value="desc">Desc</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="card-title">User Group List</h4>
                            <h6 class="card-subtitle" v-if="user_groups.total">Total {{user_groups.total}} result found!</h6>
                            <h6 class="card-subtitle" v-else>No result found!</h6>
                            <div class="table-responsive">
                                <table class="table" v-if="user_groups.total">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Postal Code</th>
                                            <th>Role</th>
                                            <th>Member At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="user_group in user_groups.data">
                                            <td v-text="getUserGroupName(user_group)"></td>
                                            <td v-text="getUserGroupPostalCode(user_group)"></td>
                                            <td v-html="getUserGroupRole(user_group)"></td>
                                            <td v-text="user_group.created_at"></td>
                                            <td>
                                                <button v-if="isGroupActivate(user_group) == 0" class="btn btn-primary btn-sm" @click.prevent="modalActiveGroupMember(user_group)" data-toggle="tooltip" title="Active Group Member"><i class="fa fa-user"></i></button>
                                                <button v-else class="btn btn-secondary btn-sm" @click.prevent="modalDeactiveGroupMember(user_group)" data-toggle="tooltip" title="Deactive Group Member"><i class="fa fa-gear"></i></button> 

                                                <button v-if="isGroupManager(user_group) == 0" class="btn btn-success btn-sm" @click.prevent="modalMakeGroupManager(user_group)" data-toggle="tooltip" title="Make Group Manager"><i class="fa fa-check"></i></button>
                                                <button v-else class="btn btn-secondary btn-sm" @click.prevent="modalDisableGroupManager(user_group)" data-toggle="tooltip" title="Disable Group Manager"><i class="fa fa-times"></i></button> 

                                                <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteUserGroup(user_group)" data-toggle="tooltip" title="Delete User Group"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-md-8">
                                        <pagination :data="user_groups" :limit=3 v-on:pagination-change-page="getUserGroups"></pagination>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="float-right">
                                            <select name="pageLength" class="form-control" v-model="filterUserGroupForm.pageLength" @change="getUserGroups" v-if="user_groups.total">
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
    </form>
</template>

<script>
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { pagination, ClickConfirm },
        data() {
            return {
                countries : {},
                user_groups: {},
                userForm: new Form({
                    'avatar' : '',
                    'email' : '',
                    'full_name' : '',
                    'street_address' : '',
                    'postal_code' : '',
                    'country' : '',
                    'created_at' : ''
                }),

                filterUserGroupForm: {
                    sortBy : 'created_at',
                    order: 'desc',
                    user_id: this.id,
                    name : '',
                    postal_code : '',
                    admin : -1,
                    status : '',
                    pageLength: 100
                },

                group_id: 0,
                user_data : {},
                
                groupActivePermission : 1,
                groupManagerPermission : 1,
            };
        },
        props: ['id'],
        mounted() {
            if(this.id != 0)
                this.getUser();
        },
        created() {
            this.getCountries();
            this.getUserGroups();
        },
        methods: {
            proceed(){
                if(this.id != 0)
                    this.getUser();
            },
            getCountries() {
                this.baseUrl = window.location.origin;
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
            getUserGroups(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterUserGroupForm);
                axios.get('/admin/usergroup?&page=' + page + url).then(response => {
                    this.user_groups = response.data;
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
            getUser(){
                axios.get('/admin/user/'+this.id)
                .then(response => {
                    this.user_data = response.data.data;
                    this.userForm.avatar = this.user_data.profile.avatar ? this.user_data.profile.avatar : "";
                    this.userForm.email = this.user_data.email ? this.user_data.email : "";
                    this.userForm.full_name = this.user_data.profile.full_name ? this.user_data.profile.full_name : "";
                    this.userForm.street_address = this.user_data.profile.street_address ? this.user_data.profile.street_address : "";
                    this.userForm.postal_code = this.user_data.profile.postal_code ? this.user_data.profile.postal_code : "";
                    this.userForm.country = this.user_data.profile.country ? this.user_data.profile.country : "";
                    this.userForm.created_at = this.user_data.profile.created_at ? this.user_data.profile.created_at : "";
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
            getSelectedCountryStatus(country) {
                if (country == this.userForm.country) {
                    return true;
                } else {
                    return false;
                }
            },
            isGroupActivate(user_group) {
                if (user_group) {
                    if (user_group.status == 'activated') {
                        return 1;
                    }
                }
                return 0;
            },
            isGroupManager(user_group) {
                if (user_group) {
                    if (user_group.admin == '1') {
                        return 1;
                    }
                }
                return 0;
            },
            getUserGroupName(user_group) {
                if (user_group) {
                    if (user_group.group) {
                        return user_group.group.name;
                    }
                }
                return '';
            },
            getUserGroupPostalCode(user_group) {
                if (user_group) {
                    if (user_group.group) {
                        return user_group.group.postal_code;
                    }
                }
                return '';
            },
            getUserGroupRole(user_group) {
                let userRoleHtml = '';
                if (user_group.status == 'activated') {
                    if (user_group.admin == "1") {
                        userRoleHtml = '<span class="label label-primary">Manager</span>';
                    } else {
                        userRoleHtml = '<span class="label label-success">User</span>';
                    }
                } else {
                    if (user_group.admin == "1") {
                        userRoleHtml = '<span class="label label-light-primary">Manager</span>';
                    } else {
                        userRoleHtml = '<span class="label label-light-success">User</span>';
                    }
                }
                return userRoleHtml;
            },
            
            modalActiveGroupMember(user_group) {
                this.group_id = user_group.id;
                $('#modal-active-group').modal('show');
            },
            activeGroupMember() {
                axios.post('/admin/usergroup/active/' + this.id + '/' + this.group_id).then(response => {
                    $('#modal-active-group').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUserGroups();
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
            
            modalDeactiveGroupMember(user_group) {
                this.group_id = user_group.id;
                $('#modal-deactive-group').modal('show');
            },
            deactiveGroupMember() {
                axios.post('/admin/usergroup/deactive/' + this.id + '/' + this.group_id).then(response => {
                    $('#modal-deactive-group').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUserGroups();
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

            modalMakeGroupManager(user_group) {
                this.group_id = user_group.id;
                $('#modal-make-group-manager').modal('show');
            },
            makeGroupManager() {
                axios.post('/admin/usergroup/manager/' + this.id + '/' + this.group_id).then(response => {
                    $('#modal-make-group-manager').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUserGroups();
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

            modalDisableGroupManager(user_group) {
                this.group_id = user_group.id;
                $('#modal-disable-group-manager').modal('show');
            },
            disableGroupManager() {
                axios.post('/admin/usergroup/user/' + this.id + '/' + this.group_id).then(response => {
                    $('#modal-disable-group-manager').modal('hide');
                    toastr['success'](response.data.message);
                    this.getUserGroups();
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
        },
        computed: {
            getAvatar(){
                if (this.userForm.avatar) {
                    return '/images/users/'+ this.userForm.avatar;
                } else {
                    return '/images/common/no-user.png';
                }                
            }
        }
    }
</script>
