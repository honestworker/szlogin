<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Administrator</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item active">Administrator</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Administrator</h4>                        
                        <div class="row m-t-20">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Full Name</label>
                                    <input class="form-control" v-model="filterAdminForm.full_name" @change="getAdmins">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Street Address</label>
                                    <input class="form-control" v-model="filterAdminForm.street_address" @change="getAdmins">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Zip Code</label>
                                    <input class="form-control" v-model="filterAdminForm.postal_code" @change="getAdmins">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Country</label>
                                    <select name="country" class="form-control" v-model="filterAdminForm.country" @change="getAdmins">
                                        <option value="0">All</option>
                                        <option v-for="country in countries.countries" v-bind:value="country">{{country}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterAdminForm.sortBy" @change="getAdmins">
                                        <option value="full_name">Full Name</option>
                                        <option value="email">Email</option>
                                        <option value="postal_code">Zip Code</option>
                                        <option value="country">Country</option>
                                        <option value="created_at">Created At</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterAdminForm.order" @change="getAdmins">
                                        <option value="asc">Asc</option>
                                        <option value="desc">Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="card-title">Administrator List</h4>
                        <h6 class="card-subtitle" v-if="admins.total">Total {{admins.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="admins.total">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Street Address</th>
                                        <th>Zip Code</th>
                                        <th>Country</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="admin in admins.data">
                                        <td class="user-profile" v-html="getAdmin(admin)"></td>
                                        <td v-text="getAdminStreetAddress(admin)"></td>
                                        <td v-text="getAdminZipCode(admin)"></td>
                                        <td v-text="getAdminCountry(admin)"></td>
                                        <td v-html="getAdminStatus(admin)"></td>
                                        <td v-text="admin.created_at"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="viewAdminProfile(admin)" data-toggle="tooltip" title="View User Profile"><i class="fa fa-eye"></i></button>

                                            <span v-if="isAdministrator(admin) > -1">
                                                <button v-if="isAdministrator(admin) == 0" class="btn btn-success btn-sm" @click.prevent="modalMakeAdministrator(admin)" data-toggle="tooltip" title="Make Administrator"><i class="fa fa-check"></i></button>
                                                <button v-else class="btn btn-secondary btn-sm" @click.prevent="modalDisableAdministrator(admin)" data-toggle="tooltip" title="Disable Administrator"><i class="fa fa-times"></i></button> 
                                            </span>
                                            <span v-else>
                                                <button class="btn btn-secondary btn-sm" @click.prevent="modalMakeAdministrator(admin)" data-toggle="tooltip" title="Make Administrator" disabled><i class="fa fa-check"></i></button>
                                            </span>

                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteAdmin(admin)" data-toggle="tooltip" title="Delete User"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="admins" :limit=3 v-on:pagination-change-page="getAdmins"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterAdminForm.pageLength" @change="getAdmins" v-if="admins.total">
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
        
        <!-- Delete Administrator Modal -->
        <div class="modal" id="modal-delete-admin" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingAdmin">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete Administrator
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this Administrator?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteAdmin()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Make Administrator -->
        <div class="modal" id="modal-administrator" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="adminPermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Make Administrator Permission
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to make this User as the Administrator?
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
            <div class="modal-dialog" v-if="adminPermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Disable Administrator Permission
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to disable this User as the Administrator?
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
                admins: {},
                countries: {},

                filterAdminForm: {
                    sortBy : 'created_at',
                    order: 'desc',
                    full_name : '',
                    email : '',
                    street_address : '',
                    postal_code : '',
                    country : 0,
                    backend : 1,
                    created_at : '',
                    pageLength: 100
                },

                deletingAdmin : 1,
                adminPermission : 1,
                admin_id: 0
            }
        },
        mounted() {
            this.getCountries();
            this.getAdmins();
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
            getAdmins(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterAdminForm);
                axios.get('/admin/user?&page=' + page + url).then(response => {
                    this.admins = response.data;
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
            getAdmin(admin) {
                var adminHtml = "";
                if (typeof admin != 'undefined' && admin !== null && admin !== '') {
                    adminHtml = "<div class='profile-img'>";
                    if (typeof admin.profile != 'undefined' && admin.profile !== null && admin.profile !== '') {
                        if (admin.profile.avatar) {
                            adminHtml = adminHtml + "<img src='/images/users/" + admin.profile.avatar + "' alt='user'>";
                        } else {
                            adminHtml = adminHtml + "<img src='/images/common/no-user.png' alt='user'>";
                        }
                        adminHtml = adminHtml + "</div>";
                        adminHtml = adminHtml + "<p style='margin-bottom: 0px'>" + admin.profile.full_name + "</p>";
                        adminHtml = adminHtml + "<p style='margin-bottom: 0px'>" + admin.email + "</p>";
                    } else {
                        adminHtml = adminHtml + "<img src='/images/common/no-user.png' alt='user'></div>";
                    }
                }
                return adminHtml;
            },
            getAdminStreetAddress(admin) {
                if(admin.profile)
                    return admin.profile.street_address;
                    
                return '';
            },
            getAdminZipCode(admin) {
                if(admin.profile)
                    return admin.profile.postal_code;
                    
                return '';
            },
            getAdminCountry(admin) {
                if(admin.profile)
                    return admin.profile.country;
                    
                return '';
            },
            getAdminStatus(admin){
                if(admin.status == 'pending')
                    return '<span class="label label-warning">Pending</span>';
                else if(admin.status == 'activated')
                    return '<span class="label label-success">Activated</span>';
                else if(admin.status == 'banned')
                    return '<span class="label label-danger">Banned</span>';
                else
                    return;
            },
            viewAdminProfile(admin) {
                this.$router.push('/admin/' + admin.id + '/view');
            },

            modalDeleteAdmin(admin) {
                this.admin_id = admin.id;
                $('#modal-delete-admin').modal('show');
            },
            deleteAdmin() {
                axios.delete('//admin/user/' + this.admin_id).then(response => {
                    $('#modal-delete-admin').modal('hide');
                    toastr['success'](response.data.message);
                    this.getAdmins();
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
                    $('#modal-delete-admin').modal('hide');
                });
            },

            isAdministrator(admin) {
                if(admin.status == 'pending')
                    return 0;
                else if(admin.status == 'activated')
                    return 1;
            },

            modalMakeAdministrator(admin) {
                this.admin_id = admin.id;
                $('#modal-administrator').modal('show');
            },
            makeAdministrator() {
                axios.patch('/admin/user/admin/' + this.admin_id).then(response => {
                    $('#modal-administrator').modal('hide');
                    toastr['success'](response.data.message);
                    this.getAdmins();
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
                    $('#modal-administrator').modal('hide');
                });
            },

            modalDisableAdministrator(admin) {
                this.admin_id = admin.id;
                $('#modal-disable-administrator').modal('show');
            },
            disableAdministrator() {
                axios.delete('/admin/user/admin/' + this.admin_id).then(response => {
                    $('#modal-disable-administrator').modal('hide');
                    toastr['success'](response.data.message);
                    this.getAdmins();
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
                    $('#modal-disable-administrator').modal('hide');
                });
            },
        },
    }
</script>
