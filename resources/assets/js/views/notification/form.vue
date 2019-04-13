<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="form-group col-md-6 col-lg-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Type</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <select name="type" class="form-control" v-model="notificationForm.type">
                                    <option v-for="(type, index) in ntf_types.data" v-bind:value="type.id" v-bind:selected="getSeletedType(type.id, index)">{{type.name}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Group ID</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <span v-text="notificationForm.group_id"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">User Full Name</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <span v-text="notificationForm.full_name"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Email</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <span v-text="notificationForm.email"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Contents</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <textarea v-model="notificationForm.contents" class="form-control" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="row" v-if="id != 0">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Created At</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <span v-text="notificationForm.created_at"></span>
                            </div>
                        </div>
                        <div class="row hide" v-else>
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Created At</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <span v-text="notificationForm.created_at"></span>
                            </div>
                        </div>
                        <div class="row">
                            <label for="logo" class="control-label">Images</label>
                            <br><br>
                            <div class="col-md-12">
                                <input type="file" multiple="multiple" id="uploadImages" @change="uploadFieldChange">
                                <p>You must upload the jpg, jpeg, png, gif file.</p>
                                <hr>
                            </div>
                            <div class="col-md-12">
                                <div class="attachment-holder animated fadeIn" v-cloak v-for="(image, index) in uploadImages"> 
                                    <span class="label label-primary">{{ image.name + ' (' + Number((image.size / 1024).toFixed(1)) + 'KB)'}}</span> 
                                    <span class="" style="background: red; cursor: pointer;" @click.prevent="removeUploadImage(image)"><button class="btn btn-xs btn-danger">Remove</button></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4" v-for="image in notificationForm.images" v-bind:id="image.id">
                                        <img v-bind:src="getNtfImage(image)" class="img-responsive">
                                        <span class="text-center" style="background: red; cursor: pointer; display: block; width: 100%" @click="modalDeleteImage"><button type="button" class="btn btn-xs btn-danger">Delete</button></span>
                                    </div>
                                </div>
                            </div>
                            <br><br>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-info waves-effect waves-light m-t-10">
                                    <span v-if="id != 0">Update</span>
                                    <span v-else>Create</span>
                                </button>
                                <router-link to="/notification" class="btn btn-danger waves-effect waves-light m-t-10">Cancel</router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6 col-lg-6 col-sm-12" v-if="id != 0">
                <label for="logo" class="control-label">Comments</label>
            </div>
        </div>

        <!-- Delete Image Modal -->
        <div class="modal" id="modal-delete-image" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingImage">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete Image
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this Image?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteImage()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
<script>
    import ClickConfirm from 'click-confirm'
    import helper from '../../services/helper'
    
    export default {
        components : { ClickConfirm },
        data() {
            return {
                uploadImages: [],
                uploadDataForm: [],
                percentCompleted: 0,

                ntf_types : {},
                comments : {},
                profile : {},
                notificationForm: new Form({
                    'group_id' : '',
                    'type' : '',
                    'full_name' : '',
                    'email' : '',
                    'contents' : '',
                    'created_at' : '',
                    'images' : {},
                    'comments' : {},
                }),

                deleteImageEl: '',
                deletingImage: 1,

                baseUrl : '',
            }
        },
        created() {
            this.getNtfTypes();
            this.getProfile();
        },
        props: ['id'],
        mounted() {
            if (this.id != 0) {
                this.updateNotification();
            } else {
                this.storeNotification();
            }
        },

        methods: {
            getNowDateTime() {
                var now     = new Date(); 
                var year    = now.getFullYear();
                var month   = now.getMonth()+1; 
                var day     = now.getDate();
                var hour    = now.getHours();
                var minute  = now.getMinutes();
                var second  = now.getSeconds(); 
                if(month.toString().length == 1) {
                    month = '0' + month;
                }
                if(day.toString().length == 1) {
                    day = '0' + day;
                }   
                if(hour.toString().length == 1) {
                    hour = '0' + hour;
                }
                if(minute.toString().length == 1) {
                    minute = '0' + minute;
                }
                if(second.toString().length == 1) {
                    second = '0' + second;
                }   
                var dateTime = year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;   
                return dateTime;
            },
            proceed() {
                this.uploadDataForm = new FormData();
                if (this.id != 0) {
                    this.storeNotification();
                } else {
                    this.updateNotification();
                }
            },
            getNtfTypes() {
                this.baseUrl = window.location.origin;
                axios.get('/admin/noti_type').then(response => {
                    this.ntf_types = response.data;
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
            getProfile() {
                axios.get('/admin/user/profile').then(response => {
                    if (response.data.data) {
                        this.notificationForm.group_id = response.data.data.group_id;
                        this.notificationForm.email = response.data.data.email;
                        if (response.data.data.profile) {
                            this.notificationForm.full_name = response.data.data.profile.full_name;
                        }
                    }
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
            getSeletedType(type, index) {
                if (this.id != 0) {
                    if (type == this.notificationForm.type) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if (index == 0) {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
            getNotification() {
                axios.post('/admin/notification/' + this.id)
                .then(response => {
                    this.notificationForm.group_id = response.data.notification.group.group_id;
                    this.notificationForm.type = response.data.notification.type;
                    this.notificationForm.full_name = response.data.notification.user.profile.full_name;
                    this.notificationForm.email = response.data.notification.user.email;
                    this.notificationForm.contents = response.data.notification.contents;
                    this.notificationForm.created_at = response.data.notification.created_at;
                    this.notificationForm.images = response.data.notification.images;
                    this.comments = response.data.notification.comments;
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
            storeNotification() {
                this.prepareFields();
                var config = {
                    headers: { 'Content-Type': 'multipart/form-data' } ,
                    onUploadProgress: function(progressEvent) {
                        this.percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total );
                        this.$forceUpdate();
                    }.bind(this)
                };

                axios.post('/admin/create-notification', this.uploadDataForm, config)
                .then(response => {
                    toastr['success'](response.data.message);
                    this.$router.push('/notification');
                }).catch(error => {
                    if (error.status == 'fail') {
                        if (error.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            prepareFields() {
                if (this.uploadImages.length > 0) {
                    for (var i = 0; i < this.uploadImages.length; i++) {
                        let image = this.uploadImages[i];
                        this.uploadDataForm.append('images[]', image);
                    }
                }
                if (this.id != 0) {
                    this.uploadDataForm.append('notification_id', this.id);
                }
                this.uploadDataForm.append('type', this.notificationForm.type);
                this.uploadDataForm.append('contents', this.notificationForm.contents);
                if (this.id != 0) {
                    this.uploadDataForm.append('datetime', this.notificationForm.created_at);
                } else {
                    this.uploadDataForm.append('datetime', this.getNowDateTime());
                }
            },
            getUploadImageSize() {
                this.upload_size = 0;
                this.uploadImages.map((item) => { this.upload_size += parseInt(item.size); });
                
                this.upload_size = Number((this.upload_size).toFixed(1));
                this.$forceUpdate();
            },
            removeUploadImage(image) {
                this.uploadImages.splice(this.uploadImages.indexOf(image), 1);                
                this.getUploadImageSize();
                $('#uploadImages').val('');
            },
            modalDeleteImage(e) {
                this.deleteImageEl = e.target.parentElement;
                $('#modal-delete-image').modal('show');
            },
            deleteImage() {
                for (var index = 0; index < this.notificationForm.images.length; index++) {
                    if (this.notificationForm.images[index]['id'] == this.deleteImageEl.id) {
                        this.notificationForm.images.splice(index, 1);
                    }
                }
                $('#modal-delete-image').modal('hide');
            },
            getNtfImage(image) {
                if (image.url) {
                    return this.baseUrl + '/images/notifications/' + image.url;
                } else {
                    return this.baseUrl + '/images/common/no-image.png';
                }
            },
            
            uploadFieldChange(e) {
                if ( $('#uploadImages').val() ) {
                    var files = e.target.files || e.dataTransfer.files;
                    if (!files.length)
                        return;
                    for (var i = files.length - 1; i >= 0; i--) {
                        this.uploadImages.push(files[i]);
                    }
                    $('#uploadImages').val('');
                }
            },
            
            start() {
                console.log('Starting File Management Component');
            },
        },
        computed: {
            
        }
    }
</script>