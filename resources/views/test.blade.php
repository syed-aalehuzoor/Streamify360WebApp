@extends('layouts.app')

@section('content')
<div class="container p-6">
    <h2 class="mb-4">Cloudflare Service Test</h2>
    
    @if(session('message'))
        <div class="alert alert-info">{{ session('message') }}</div>
    @endif
    
    <?php
    $domain = request()->getHost();
    echo $domain;
    ?>
    <form method="POST" action="{{ route('test.cloudflare') }}">
        @csrf
        <div class="mb-3">
            <label for="domain" class="form-label">Domain Name</label>
            <input type="text" name="domain" id="domain" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label for="server_ip" class="form-label">Server IP (For A Record)</label>
            <input type="text" name="server_ip" id="server_ip" class="form-control">
        </div>
        
        <div class="mb-3">
            <label for="action" class="form-label">Select Action</label>
            <select name="action" id="action" class="form-select" required>
                <option value="get_zone">Get Zone by Domain</option>
                <option value="create_zone">Create Zone</option>
                <option value="delete_zone">Delete Zone</option>
                <option value="delete_dns_records">Delete All DNS Records</option>
                <option value="create_a_record">Create A Record</option>
                <option value="run_activation_check">Run Activation Check</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection
