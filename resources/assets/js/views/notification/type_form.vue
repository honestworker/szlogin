<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="">Name</label>
                    <input class="form-control" type="text" value="" v-model="notificationTypeFrom.name">
                </div>
                <div class="form-group">
                    <label for="">Trans Name</label>
                    <input class="form-control" type="text" value="" v-model="notificationTypeFrom.trans_name">
                </div>
            </div>
        </div>
        <button class="btn btn-info waves-effect waves-light m-t-10">
            <span v-if="id" @click="updateNotification">Update</span>
            <span v-else @click="storeNotification">Create</span>
        </button>
        <button class="btn btn-danger waves-effect waves-light m-t-10" v-show="id" @click="cancelNotification">Cancel</button>
    </form>
</template>

<script>
    import helper from '../../services/helper'

    export default {
        data() {
            return {
                notificationTypeFrom: new Form({
                    'name' : '',
                    'trans_name' : '',
                    'created_at' : '',
                }),
            };
        },
        props: ['id', 'name', 'created_at'],
        mounted() {
        },
        watch: {
            id : function(val) {
                this.notificationTypeFrom.name = this.name;
                this.notificationTypeFrom.trans_name = this.trans_name;
                this.notificationTypeFrom.created_at = this.created_at;
            }
        },
        methods: {
            proceed(){
            },
            cancelNotification() {
                if(this.id) {
                    this.$emit('interface');
                }
            },
            storeNotification(){
                this.notificationTypeFrom.post('/api/noti_type')
                .then(response => {
                    toastr['success'](response.message);
                    this.$emit('completed',response.noti_type)
                }).catch(error => {
                    if (error.response.data) {
                        if (error.response.data.message) {
                            toastr['error'](error.response.data.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                        }
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
            },
            updateNotification(){
                this.notificationTypeFrom.patch('/api/noti_type/'+this.id)
                .then(response => {
                    if(response.type == 'error') {
                        if (response.message) {
                            toastr['error'](response.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                        }
                    } else {
                        toastr['success'](response.message);
                        this.$emit('completed',response.noti_type)
                    }
                }).catch(error => {
                    if (error.response.data) {
                        if (error.response.data.message) {
                            toastr['error'](error.response.data.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                        }
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
                this.cancelNotification();
            }
        }
    }
</script>
