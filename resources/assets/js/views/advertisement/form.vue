<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="form-group col-md-4">
                <label for="logo" class="control-label">Attachments</label>
                <br><br>
                <div class="col-md-12">
                    <input type="file" multiple="multiple" id="uploadImages" @change="uploadFieldChange">
                    <p>You must upload the jpg, jpeg, png, gif file.</p>
                    <hr>
                    <div class="col-md-12">
                        <div class="attachment-holder animated fadeIn" v-cloak v-for="(image, index) in uploadImages"> 
                            <span class="label label-primary">{{ image.name + ' (' + Number((image.size / 1024).toFixed(1)) + 'KB)'}}</span> 
                            <span class="" style="background: red; cursor: pointer;" @click="removeAttachment(image)"><button class="btn btn-xs btn-danger">Remove</button></span>
                        </div>
                        <div class="form-group text-center" id="previewImage">
                            <img :src="previewImage" class="img-responsive img-max-width-200">
                        </div>
                    </div>
                </div>
                <br><br>
                <button type="submit" class="btn btn-info waves-effect waves-light m-t-10">
                    <span v-if="id != 0">Update</span>
                    <span v-else>Create</span>
                </button>
                <router-link to="/advertisement" class="btn btn-danger waves-effect waves-light m-t-10">Cancel</router-link>
            </div>
            <div class="form-group col-md-8">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="">Name</label>
                            <input class="form-control" type="text" value="" v-model="advertisementForm.name">
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                            <label for="">Link</label>
                            <input class="form-control" type="text" value="" v-model="advertisementForm.link">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Start Date</label>
                            <datepicker v-model="advertisementForm.start_date" :bootstrapStyling="true"></datepicker>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">End Date</label>
                            <datepicker v-model="advertisementForm.end_date"  :bootstrapStyling="true"></datepicker>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Country</label>
                            <select name="status" class="form-control" v-model="advertisementForm.country" @change="changeCountry">
                                <option v-for="country in countries.countries" v-bind:value="country" v-bind:selected="getSelectedStatus(country)">{{country}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Postal Code Min</label>
                            <input class="form-control" type="text" value="" v-model="advertisementForm.min_postal">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Postal Code Max</label>
                            <input class="form-control" type="text" value="" v-model="advertisementForm.max_postal">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
<script>
    import datepicker from 'vuejs-datepicker'
    import ClickConfirm from 'click-confirm'
    import helper from '../../services/helper'
    
    export default {
        components : { datepicker, ClickConfirm },
        data() {
            return {
                uploadImages: [],
                uploadDataForm: [],
                errors: {
                },
                percentCompleted: 0,

                countries : {},
                advertisementForm: new Form({
                    'name' : '',
                    'link' : '',
                    'start_date' : '',
                    'end_date' : '',
                    'country' : '',
                    'min_postal' : '',
                    'max_postal' : '',
                }),

                previewImage: '',

                baseUrl : '',
            }
        },
        created() {
            this.getCountries();
        },
        props: ['id'],
        mounted() {
            if(this.id != 0) {
                this.getAdvertisement();
            }
        },

        methods: {
            processDataTimes() {
                if (this.advertisementForm.start_date) {
                    this.advertisementForm.start_date = moment(this.advertisementForm.start_date).format('YYYY-MM-DD');
                } else {
                    this.advertisementForm.start_date = '';
                }
                if (this.advertisementForm.end_date) {
                    this.advertisementForm.end_date = moment(this.advertisementForm.end_date).format('YYYY-MM-DD');
                } else {
                    this.advertisementForm.end_date = '';
                }
            },
            proceed() {
                this.uploadDataForm = new FormData();
                // if(this.id != 0) {
                //     this.updateAdvertisement();
                // } else {
                //     this.storeAdvertisement();
                // }
                this.storeAdvertisement();
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
            changeCountry(e) {
                if(e.target.options.selectedIndex > -1) {
                    this.advertisementForm.country = e.target.options[e.target.options.selectedIndex].value;
                }
            },
            getSelectedStatus(country) {
                if (country == this.advertisementForm.country) {
                    return true;
                } else {
                    return false;
                }
            },
            getAdvertisement(){
                axios.get('/admin/advertisement/' + this.id)
                .then(response => {
                    if (response.data.image) {
                        this.previewImage = this.baseUrl + '/images/advertisements/' + response.data.image;
                    } else {
                        this.previewImage = this.baseUrl + '/images/common/no-image.png';
                    }
                    this.advertisementForm.name = response.data.name;
                    this.advertisementForm.link = response.data.link;
                    this.advertisementForm.start_date = (response.data.start_date != "") ? response.data.start_date : 0;
                    this.advertisementForm.end_date = (response.data.end_date != "") ? response.data.end_date : 0;
                    this.advertisementForm.country = response.data.country;
                    this.advertisementForm.min_postal = (response.data.min_postal != '') ? response.data.min_postal : '';
                    this.advertisementForm.max_postal = (response.data.max_postal != '') ? response.data.max_postal : '';
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
            updateAdvertisement() {
                this.prepareFields();
                var config = {
                    headers: { 'Content-Type': 'multipart/form-data' } ,
                    onUploadProgress: function(progressEvent) {
                        this.percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total );
                        this.$forceUpdate();
                    }.bind(this)
                };
                
                axios.patch('/admin/advertisement/' + this.id, this.uploadDataForm, config)
                .then(response => {
                    toastr['success'](response.data.message);
                    this.$router.push('/advertisement');
                }).catch(error => {
                    if (error.status == 'fail') {
                        if (error.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.message);
                        }
                    } else if (error.response.data.status == 'fail') {
                        toastr['error'](error.response.data.message);
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            storeAdvertisement() {
                this.prepareFields();
                var config = {
                    headers: { 'Content-Type': 'multipart/form-data' } ,
                    onUploadProgress: function(progressEvent) {
                        this.percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total );
                        this.$forceUpdate();
                    }.bind(this)
                };
                
                axios.post('/admin/advertisement', this.uploadDataForm, config)
                .then(response => {
                    toastr['success'](response.data.message);
                    this.$router.push('/advertisement');
                }).catch(error => {
                    if (error.status == 'fail') {
                        if (error.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.message);
                        }
                    } else if (error.response.data.status == 'fail') {
                        toastr['error'](error.response.data.message);
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            getAttachmentSize() {
                this.upload_size = 0;
                this.uploadImages.map((item) => { this.upload_size += parseInt(item.size); });
                
                this.upload_size = Number((this.upload_size).toFixed(1));
                this.$forceUpdate();
            },
            prepareFields() {
                this.processDataTimes();
                if (this.uploadImages.length > 0) {
                    for (var i = 0; i < this.uploadImages.length; i++) {
                        let image = this.uploadImages[i];
                        this.uploadDataForm.append('images[]', image);
                        break;
                    }
                }
                if (this.id != 0) {
                    this.uploadDataForm.append('id', this.id);
                }
                this.uploadDataForm.append('name', this.advertisementForm.name);
                this.uploadDataForm.append('link', this.advertisementForm.link);
                if (this.advertisementForm.start_date)
                    this.uploadDataForm.append('start_date', this.advertisementForm.start_date);
                if (this.advertisementForm.end_date)
                    this.uploadDataForm.append('end_date', this.advertisementForm.end_date);
                this.uploadDataForm.append('country', this.advertisementForm.country);
                if (this.advertisementForm.min_postal)
                    this.uploadDataForm.append('min_postal', this.advertisementForm.min_postal);
                if (this.advertisementForm.max_postal)
                    this.uploadDataForm.append('max_postal', this.advertisementForm.max_postal);
            },
            removeAttachment(image) {                
                this.uploadImages.splice(this.uploadImages.indexOf(image), 1);                
                this.getAttachmentSize();
                $('#previewImage').addClass('hide');
            },
            
            uploadFieldChange(e) {
                var files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                for (var i = files.length - 1; i >= 0; i--) {
                    this.uploadImages.push(files[i]);
                }
                $('#previewImage').addClass('hide');
            },
            
            start() {
                console.log('Starting File Management Component');
            },
        },
        computed: {
            
        }
    }
</script>