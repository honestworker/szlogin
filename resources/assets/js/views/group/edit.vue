<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Edit Group</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">Home</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/task">Group</router-link></li>
                    <li class="breadcrumb-item active">Edit Group</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Group</h4>
                        <group-form :id="id"></group-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import GroupForm from './form';
    import helper from '../../services/helper'

    export default {
        components : { GroupForm },
        data() {
            return {
                id:this.$route.params.id
            }
        },
    }
</script>