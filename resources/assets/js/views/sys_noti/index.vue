<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-12 col-12 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">System Notification</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item active">System Notification</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <router-link to="/sys_noti/0" class="btn btn-success waves-effect waves-light m-t-10">Create System Notification</router-link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Notification</h4>
                        <div class="row m-t-20">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Country</label>
                                    <select name="country" class="form-control" v-model="filterNtfForm.country" @change="getNtfs">
                                        <option value="">All</option>
                                        <option v-for="country in countries.countries" v-bind:value="country">{{country}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Group ID</label>
                                    <input class="form-control" v-model="filterNtfForm.group_id" @change="getNtfs">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input class="form-control" v-model="filterNtfForm.email" @change="getNtfs">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Contents</label>
                                    <input class="form-control" v-model="filterNtfForm.contents" @change="getNtfs">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterNtfForm.status" @change="getNtfs">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterNtfForm.sortBy" @change="getNtfs">
                                        <option value="country">Country</option>
                                        <option value="email">Email</option>
                                        <option value="contents">Contents</option>
                                        <option value="created_at" selected>Created At</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterNtfForm.order" @change="getNtfs">
                                        <option value="asc">Asc</option>
                                        <option value="desc" selected>Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Notification List</h4>
                        <h6 class="card-subtitle" v-if="ntfs.total">Total {{ntfs.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="ntfs.total">
                                <thead>
                                    <tr>
                                        <th>Country</th>
                                        <th>Group ID</th>
                                        <th>Email</th>
                                        <th>Contents</th>
                                        <th>Images</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ntf in ntfs.data">
                                        <td v-text="getNtfCountry(ntf)"></td>
                                        <td v-text="getNtfGroupID(ntf)"></td>
                                        <td v-html="getNtfEmail(ntf)"></td>
                                        <td v-text="ntf.contents"></td>
                                        <td v-html="getNtfImages(ntf)"></td>
                                        <td v-text="ntf.created_at"></td>
                                        <td v-html="getNtfStatus(ntf)"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="viewNtf(ntf)" data-toggle="tooltip" title="Edit Notification"><i class="fa fa-pencil"></i></button>
                                            <button v-if="ntf.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleNtfStatus(ntf)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleNtfStatus(ntf)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteNtf(ntf)" data-toggle="tooltip" title="Delete Notification"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="ntfs" :limit=3 v-on:pagination-change-page="getNtfs"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterNtfForm.pageLength" @change="getNtfs" v-if="ntfs.total">
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
        
        <!-- Delete Notification Modal -->
        <div class="modal" id="modal-delete-ntf" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingNtf">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete Notification
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this Notification?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteNtf()">
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
                ntfs: {},
                countries: {},
                groups: {},
                filterNtfForm: {
                    sortBy : 'created_at',
                    order: 'desc',
                    country : '',
                    group_id : '',
                    email : '',
                    contents: '',
                    created_at : '',
                    status: '',
                    pageLength: 100
                },
                ntf_id : 0,
                has_group : 0,
                deletingNtf : 1,
                
                baseUrl : '',
            }
        },

        created() {
            this.getCountries();
            this.getGroups();
            this.getNtfs();
        },

        methods: {
            getCountries() {
                this.baseUrl = window.location.origin;
                axios.post('/api/country/all').then(response => {
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
                axios.post('/api/group/all').then(response => {
                    this.groups = response.data.data;
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
            getNtfs(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterNtfForm);
                axios.get('/api/sysnoti?page=' + page + url).then(response => {
                    this.ntfs = response.data;
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

            getNtfCountry(ntf) {
                if (typeof ntf.country != 'undefined' && ntf.country !== null && ntf.country !== '') {
                    return ntf.country;
                }
                return "All";
            },
            getNtfGroupID(ntf) {
                if (typeof ntf.group_id != 'undefined' && ntf.group_id !== null && ntf.group_id !== "0" && ntf.group_id !== 0) {
                    if(this.groups) {
                        for(var index = 0; index < this.groups.length; index++) {
                            if (this.groups[index].id == ntf.group_id) {
                                return this.groups[index].group_id;
                            }
                        }
                    }
                }
                return "All";
            },
            getNtfEmail(ntf) {
                if (typeof ntf.user != 'undefined') {
                    return ntf.user.email;
                }
                return "";
            },
            getNtfImages(ntf) {
                var image_html = "";
                if (typeof ntf.images != 'undefined') {
                    if (ntf.images.length > 0) {
                        for (var index = 0; index < ntf.images.length; index++) {
                            image_html += '<img src="' + this.baseUrl + '/images/notifications/' + ntf.images[index]['url'] + '" class="img-responsive-height img-max-height-100">';
                        }
                    }
                }
                return image_html;
            },
            getNtfStatus(ntf) {
                return (ntf.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },

            modalDeleteNtf(ntf) {
                this.ntf_id = ntf.id;
                $('#modal-delete-ntf').modal('show');
            },
            deleteNtf() {
                axios.delete('/api/notification/' + this.ntf_id).then(response => {
                    $('#modal-delete-ntf').modal('hide');
                    toastr['success'](response.data.message);
                    this.getNtfs();
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
                    $('#modal-delete-ntf').modal('hide');
                });
            },
            viewNtf(ntf) {
                this.$router.push('/sys_noti/' + ntf.id);
            },
            toggleNtfStatus(ntf) {
                axios.post('/api/notification/status', {id: ntf.id}).then((response) => {
                    this.getNtfs();
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
        },
    }
</script>
