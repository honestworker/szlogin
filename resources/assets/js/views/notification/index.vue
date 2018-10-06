<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-12 col-12 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Notification</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">Home</router-link></li>
                    <li class="breadcrumb-item active">Notification</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Notification</h4>
                        <div class="row m-t-20">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Notification Type</label>
                                    <input class="form-control" v-model="filterNotificationForm.type" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Group ID</label>
                                    <input class="form-control" v-model="filterNotificationForm.group_id" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Title</label>
                                    <input class="form-control" v-model="filterNotificationForm.title" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Content</label>
                                    <input class="form-control" v-model="filterNotificationForm.content" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input class="form-control" v-model="filterNotificationForm.eamil" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">First Name</label>
                                    <input class="form-control" v-model="filterNotificationForm.first_name" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Family Name</label>
                                    <input class="form-control" v-model="filterNotificationForm.family_name" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Phone Number</label>
                                    <input class="form-control" v-model="filterNotificationForm.phone_number" @blur="getNotifications">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterNotificationForm.status" @change="getNotifications">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterNotificationForm.sortBy" @change="getNotifications">
                                        <option value="type">Notification Type</option>
                                        <option value="title">Title</option>
                                        <option value="Email">Email</option>
                                        <option value="first_name">First Name</option>
                                        <option value="family_name">Family Name</option>
                                        <option value="phone_number">Phone Number</option>
                                        <option value="created_at">Created At</option>
                                        <option value="updated_at">Upadted At</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterNotificationForm.order" @change="getNotifications">
                                        <option value="asc">Asc</option>
                                        <option value="desc">Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Notification List</h4>
                        <h6 class="card-subtitle" v-if="notifications.total">Total {{notifications.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="notifications.total">
                                <thead>
                                    <tr>
                                        <th>Group ID</th>
                                        <th>Notification Type</th>
                                        <th>Full Name</th>
                                        <th>Phone Number</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="notification in notifications.data">
                                        <td v-text="notification.name"></td>
                                        <td v-text="notification.created_at"></td>
                                        <td v-html="getNotificationStatus(notification)"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="viewNotification(notification)" data-toggle="tooltip" title="Edit Notification"><i class="fa fa-pencil"></i></button>
                                            <button v-if="notification.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleNotificationStatus(notification)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleNotificationStatus(notification)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteNotification(notification)" data-toggle="tooltip" title="Delete Notification"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="notifications" :limit=3 v-on:pagination-change-page="getNotifications"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterNotificationForm.pageLength" @change="getNotifications" v-if="notifications.total">
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
        <div class="modal" id="modal-delete-notification-type" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingNotification">
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
                        <button type="button" class="btn btn-danger" @click.prevent="deleteNotification()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    //import NotificationViewForm from './view_form'
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { pagination, ClickConfirm }, //NotificationViewForm, 
        data() {
            return {
                notifications: {},
                filterNotificationForm: {
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
                deletingNotification : 1,
                type_id : 0
            }
        },

        created() {
            this.getNotifications();
        },

        methods: {
            getNotifications(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterNotificationForm);
                axios.get('/api/notification?page=' + page + url)
                    .then(response => this.notifications = response.data);
            },

            modalDeleteNotification(notification) {
                this.type_id = notification.id;
                $('#modal-delete-notification-type').modal('show');
            },
            deleteNotification() {
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

            editNotification(notification){
                this.id = notification.id;
                this.name = notification.name;
                this.created_at = notification.created_at;
            },
            cancelNotification(notification){
                this.id = 0;
                this.name = '';
                this.created_at = '';
            },
            getNotificationStatus(notification){
                return (notification.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },
            toggleNotificationStatus(notification) {
                axios.post('/api/noti_type/status', {id: notification.id}).then((response) => {
                    this.getNotifications();
                });
            }
        }
    }
</script>
