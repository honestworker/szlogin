<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">View User Profile</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/user">User</router-link></li>
                    <li class="breadcrumb-item active">View User Profile</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">View User Profile</h4>
                        <user-form :id="id"></user-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import UserForm from './form';
    import helper from '../../services/helper'

    export default {
        components : { UserForm },
        data() {
            return {
                id: this.$route.params.id
            }
        },
    }
</script>