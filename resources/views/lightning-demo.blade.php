@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Lightning Effect Demo</h1>
    
    <div class="lightning-effect bg-gray-900 rounded-lg w-full h-64 mb-8 relative">
        <!-- The lightning effect will be applied to this div -->
    </div>
    
    <div class="lightning-effect bg-gray-800 rounded-lg w-1/2 h-48 relative">
        <!-- Another element with lightning effect -->
    </div>
</div>
@endsection
