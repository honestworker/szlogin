<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-12 col-12 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Group</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item active">Group</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <router-link to="/group/0/edit" class="btn btn-success waves-effect waves-light m-t-10">Create Group</router-link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Group</h4>
                        <div class="row m-t-20">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Group Name</label>
                                    <input name="search" class="form-control" v-model="filterGroupForm.name" @change="getGroups">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Postal Code</label>
                                    <input name="search" class="form-control" v-model="filterGroupForm.postal_code" @change="getGroups">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Country</label>
                                    <select name="country" class="form-control" v-model="filterGroupForm.country" @change="getGroups">
                                        <option value="">All</option>
                                        <option v-for="country in countries.countries" v-bind:value="country">{{country}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterGroupForm.status" @change="getGroups">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterGroupForm.sortBy" @change="getGroups">
                                        <option value="name">Group Name</option>
                                        <option value="country">Country</option>
                                        <option value="postal_code">Postal Code</option>
                                        <option value="status">Status</option>
                                        <option value="created_at">Created At</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterGroupForm.order" @change="getGroups">
                                        <option value="asc">Asc</option>
                                        <option value="desc">Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Group List</h4>
                        <h6 class="card-subtitle" v-if="groups.total">Total {{groups.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="groups.total">
                                <thead>
                                    <tr>
                                        <th>Group Name</th>
                                        <th>Country</th>
                                        <th>Postal Code</th>
                                        <th>Members</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="group in groups.data">
                                        <td v-text="group.name"></td>
                                        <td v-text="group.country"></td>
                                        <td v-text="group.postal_code"></td>
                                        <td v-html="getGroupMembers(group)"></td>
                                        <td v-html="getGroupStatus(group)"></td>
                                        <td v-text="group.created_at"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="editGroup(group)" data-toggle="tooltip" title="Edit Group"><i class="fa fa-pencil"></i></button>
                                            <button class="btn btn-primary btn-sm" @click.prevent="viewGroupUsers(group)" data-toggle="tooltip" title="View Group Users"><i class="fa fa-eye"></i></button>
                                            <button v-if="group.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleGroupStatus(group)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleGroupStatus(group)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteGroup(group)" data-toggle="tooltip" title="Delete group"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="groups" :limit=3 v-on:pagination-change-page="getGroups"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterGroupForm.pageLength" @change="getGroups" v-if="groups.total">
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
        
        <!-- Delete Group Modal -->
        <div class="modal" id="modal-delete-group" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingGroup">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete Group
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this Group?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteGroup()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    //import GroupForm from './form'
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { pagination, ClickConfirm }, //GroupForm, 
        data() {
            return {
                groups: {},
                countries : {},
                filterGroupForm: {
                    status: '',
                    sortBy : 'created_at',
                    order: 'desc',
                    name : '',
                    country : '',
                    postal_code : '',
                    created_at : '',
                    pageLength: 100
                },
                group_id : 0,
                deletingGroup : 1
            }
        },

        created() {
            this.getGroups();
            this.getCountries();
        },

        methods: {
            getGroups(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterGroupForm);
                axios.get('/admin/group?page=' + page + url).then(response => {
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
            getGroupMembers(group) {
                let groupMembersHtml = '';
                if (group.members) {
                    groupMembersHtml = groupMembersHtml + '<span class="label label-info">' + group.members.all + '</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-primary">' + group.members.manager_active + '</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-light-primary">' + group.members.manager_deactive + '</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-success">' + group.members.user_active + '</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-light-success">' + group.members.user_deactive + '</span>';
                } else {
                    groupMembersHtml = groupMembersHtml + '<span class="label label-info">0</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-primary">0</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-light-primary">0</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-success">0</span>';
                    groupMembersHtml = groupMembersHtml + ' <span class="label label-light-success">0</span>';
                }
                return groupMembersHtml;
            },
            getGroupStatus(group) {
                return (group.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },

            modalDeleteGroup(group) {
                this.group_id = group.id;
                $('#modal-delete-group').modal('show');
            },
            deleteGroup() {
                axios.delete('/admin/group/' + this.group_id).then(response => {
                    $('#modal-delete-group').modal('hide');
                    toastr['success'](response.data.message);
                    this.getGroups();
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
                    $('#modal-delete-group').modal('hide');
                });
            },

            editGroup(group){
                this.$router.push('/group/'+group.id+'/edit');
            },
            viewGroupUsers(group){
                this.$router.push('/group/'+group.id+'/view');
            },
            toggleGroupStatus(group){
                axios.post('/admin/group/status', {id: group.id}).then((response) => {
                    this.getGroups();
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
            }
        }
    }
</script>
