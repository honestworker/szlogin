<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Group</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">Home</router-link></li>
                    <li class="breadcrumb-item active">Group</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Add new Group</h4>
                        <group-form @completed="getGroups" :groupId="groupId" :groupName="groupName" :groupDescription="groupDescription" @interface="cancelGroup"></group-form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Group</h4>
                        <div class="row m-t-40">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Search</label>
                                    <input name="search" class="form-control" v-model="filterGroupForm.search" @blur="getGroups">
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
                                        <option value="name">Name</option>
                                        <option value="status">Status</option>
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
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="group in groups.data">
                                        <td v-text="group.name"></td>
                                        <td v-text="group.description"></td>
                                        <td v-html="getGroupStatus(group)"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="editGroup(group)" data-toggle="tooltip" title="Edit Group"><i class="fa fa-pencil"></i></button>
                                            <button v-if="group.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleGroupStatus(group)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleGroupStatus(group)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="deleteGroup(group)" data-toggle="tooltip" title="Delete group"><i class="fa fa-trash"></i></button>
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
    </div>
</template>

<script>
    import GroupForm from './form'
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { GroupForm, pagination, ClickConfirm },
        data() {
            return {
                groups: {},
                filterGroupForm: {
                    status: '',
                    sortBy : 'name',
                    order: 'desc',
                    pageLength: 5
                },
                groupId: 0,
                groupName: '',
                groupDescription: '',
            }
        },

        created() {
            this.getGroups();
        },

        methods: {
            getGroups(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterGroupForm);
                axios.get('/api/group?page=' + page + url)
                    .then(response => this.groups = response.data);
                this.cancelGroup();
            },
            deleteGroup(group){
                axios.delete('/api/group/' + group.id).then(response => {
                    toastr['success'](response.data.message);
                    this.getGroups();
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            },
            editGroup(group){
                this.groupId = group.id;
                this.groupName = group.name;
                this.groupDescription = group.description;
            },
            cancelGroup(){
                this.groupId = 0;
                this.groupName = '';
                this.groupDescription = '';
            },
            getGroupStatus(group){
                return (group.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },
            toggleGroupStatus(group){
                axios.post('/api/group/status', {id:group.id}).then((response) => {
                    this.getGroups();
                });
            }
        }
    }
</script>
