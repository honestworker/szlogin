<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-12 col-12 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Notification Type</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item active">Notification Type</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Add new Notification Type</h4>
                        <notification-type-form @completed="getNotificationTypes" :id="id" :name="name" :created_at="created_at" @interface="cancelNotificationType"></notification-type-form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Notification Type</h4>
                        <div class="row m-t-40">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input class="form-control" v-model="filterNotificationTypeForm.name" @blur="getNotificationTypes">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterNotificationTypeForm.status" @change="getNotificationTypes">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterNotificationTypeForm.sortBy" @change="getNotificationTypes">
                                        <option value="name">Name</option>
                                        <option value="created_at">Created At</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterNotificationTypeForm.order" @change="getNotificationTypes">
                                        <option value="asc">Asc</option>
                                        <option value="desc">Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Notification Type List</h4>
                        <h6 class="card-subtitle" v-if="notificationtypes.total">Total {{notificationtypes.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="notificationtypes.total">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="notification_type in notificationtypes.data">
                                        <td v-text="notification_type.name"></td>
                                        <td v-text="notification_type.created_at"></td>
                                        <td v-html="getNotificationTypeStatus(notification_type)"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="editNotificationType(notification_type)" data-toggle="tooltip" title="Edit Notification Type"><i class="fa fa-pencil"></i></button>
                                            <button v-if="notification_type.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleNotificationTypeStatus(notification_type)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleNotificationTypeStatus(notification_type)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteNotificationType(notification_type)" data-toggle="tooltip" title="Delete Notification Type"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="notificationtypes" :limit=3 v-on:pagination-change-page="getNotificationTypes"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterNotificationTypeForm.pageLength" @change="getNotificationTypes" v-if="notificationtypes.total">
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
        
        <!-- Delete Notification Type Modal -->
        <div class="modal" id="modal-delete-notification-type" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingNotificationType">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete Notification Type
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this Notification Type?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteNotificationType()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import NotificationTypeForm from './type_form'
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { NotificationTypeForm, pagination, ClickConfirm },
        data() {
            return {
                notificationtypes: {},
                filterNotificationTypeForm: {
                    status: '',
                    sortBy : 'name',
                    order: 'desc',
                    name : '',
                    created_at : '',
                    pageLength: 5
                },
                id: 0,
                name: '',
                created_at: '',
                deletingNotificationType : 1,
                type_id : 0
            }
        },

        created() {
            this.getNotificationTypes();
        },

        methods: {
            getNotificationTypes(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterNotificationTypeForm);
                axios.get('/api/noti_type?page=' + page + url)
                    .then(response => this.notificationtypes = response.data);
            },

            modalDeleteNotificationType(notification_type) {
                this.type_id = notification_type.id;
                $('#modal-delete-notification-type').modal('show');
            },
            deleteNotificationType() {
                axios.delete('/api/noti_type/' + this.type_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-delete-notification-type').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.message) {
                        toastr['error'](error.response.data.message);
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
            },

            editNotificationType(notification_type){
                this.id = notification_type.id;
                this.name = notification_type.name;
                this.created_at = notification_type.created_at;
            },
            cancelNotificationType(notification_type){
                this.id = 0;
                this.name = '';
                this.created_at = '';
            },
            getNotificationTypeStatus(notification_type){
                return (notification_type.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },
            toggleNotificationTypeStatus(notification_type) {
                axios.post('/api/noti_type/status', {id: notification_type.id}).then((response) => {
                    this.getNotificationTypes();
                });
            }
        }
    }
</script>
