<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="">Index</label>
                    <input class="form-control" type="text" value="" v-model="countryForm.idx">
                </div>
                <div class="form-group">
                    <label for="">Name</label>
                    <input class="form-control" type="text" value="" v-model="countryForm.name">
                </div>
            </div>
        </div>
        <button class="btn btn-info waves-effect waves-light m-t-10">
            <span v-if="id" @click="updateCountry">Update</span>
            <span v-else @click="storeCountry">Create</span>
        </button>
        <button class="btn btn-danger waves-effect waves-light m-t-10" v-show="id" @click="cancelCountry">Cancel</button>
    </form>
</template>

<script>
    import helper from '../../services/helper'

    export default {
        data() {
            return {
                countryForm: new Form({
                    'idx' : '',
                    'name' : '',
                }),
            };
        },
        props: ['id', 'idx', 'name'],
        mounted() {
            if(this.id != 0) {
                this.getCountries();
            }
        },
        watch: {
            id : function(val) {
                this.countryForm.idx = this.idx;
                this.countryForm.name = this.name;
            }
        },
        methods: {
            proceed(){
            },
            cancelCountry() {
                if(this.id) {
                    this.$emit('interface');
                }
            },
            storeCountry(){
                this.countryForm.post('/admin/country')
                .then(response => {
                    toastr['success'](response.message);
                    this.$emit('completed',response.country);
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
            updateCountry(){
                this.countryForm.patch('/admin/country/'+this.id)
                .then(response => {
                    toastr['success'](response.message);
                    this.$emit('completed',response.country);
                    this.cancelCountry();
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
            }
        }
    }
</script>
