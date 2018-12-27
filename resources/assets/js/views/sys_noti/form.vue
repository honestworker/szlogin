<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="form-group col-md-6 col-lg-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Country</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <select name="country" class="form-control" v-model="notificationForm.country" @change="changeCountry">
                                    <option value="">All</option>
                                    <option v-for="country in countries.countries" v-bind:value="country">{{country}}</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Group ID</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <select name="group_id" class="form-control" v-model="notificationForm.group_id">
                                    <option value="">All</option>
                                    <option v-for="group in groups" v-bind:value="group.id">{{group.group_id}}</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3 col-lg-3 col-sm-12">
                                <label for="">Contents</label>
                            </div>
                            <div class="col-md-9 col-lg-9 col-sm-12">
                                <textarea v-model="notificationForm.contents" class="form-control" rows="5"></textarea>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <button type="submit" class="btn btn-info waves-effect waves-light m-t-10">
                                <span v-if="id != 0">Update</span>
                                <span v-else>Create</span>
                            </button>
                            <router-link to="/sys_noti" class="btn btn-danger waves-effect waves-light m-t-10">Cancel</router-link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6 col-lg-6 col-sm-12">
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
                </div>
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

                countries: {},
                groups: {},
                notificationForm: new Form({
                    'country' : '',
                    'group_id' : '',
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
            this.getCountries();
            this.getGroups();
        },
        props: ['id'],
        mounted() {
            if(this.id != 0) {
                this.getNotification();
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
                    this.updateNotification();
                } else {
                    this.storeNotification();
                }
            },
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
            changeCountry(e) {
                if(e.target.options.selectedIndex > -1) {
                    var country = e.target.options[e.target.options.selectedIndex].value;
                    if (!country) {
                        country = "All";
                    }
                    axios.get('/api/country_group/' + country).then(response => {
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
                }
            },
            getNotification() {
                axios.post('/api/notification/' + this.id)
                .then(response => {
                    this.notificationForm.country = response.data.notification.country;
                    if (!response.data.notification.group_id || response.data.notification.group_id == '0') {
                        this.notificationForm.group_id = '';
                    } else {
                        this.notificationForm.group_id = response.data.notification.group_id;
                    }
                    this.notificationForm.email = response.data.notification.user.email;
                    this.notificationForm.contents = response.data.notification.contents;
                    this.notificationForm.created_at = response.data.notification.created_at;
                    this.notificationForm.images = response.data.notification.images;
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

                axios.post('/api/create-sysnoti', this.uploadDataForm, config)
                .then(response => {
                    toastr['success'](response.data.message);
                    this.$router.push('/sys_noti');
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
            updateNotification() {
                this.prepareFields();
                var config = {
                    headers: { 'Content-Type': 'multipart/form-data' } ,
                    onUploadProgress: function(progressEvent) {
                        this.percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total );
                        this.$forceUpdate();
                    }.bind(this)
                };

                axios.post('/api/update-notification', this.uploadDataForm, config)
                .then(response => {
                    toastr['success'](response.data.message);
                    this.$router.push('/sys_noti');
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
                this.uploadDataForm.append('group_id', this.notificationForm.group_id);
                this.uploadDataForm.append('country', this.notificationForm.country);
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
                if (e.target.tagName == "BUTTON") {
                    this.deleteImageEl = e.target.parentElement.parentElement;
                } else {
                    this.deleteImageEl = e.target.parentElement;
                }
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
                    return his.baseUrl + '/images/common/no-image.png';
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