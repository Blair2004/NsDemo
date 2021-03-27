<div class="relative mb-10">
    <div class=" transform -skew-x-3 rotate-3 bg-transparent bg-gradient-to-tr rounded shadow from-blue-500 to-purple-600 absolute h-full w-full"></div>
    <div class="my-2 shadow bg-white rounded p-2 z-10 relative">
        <h3 class="text-2xl text-gray-800 text-center">Stagging Installation</h3>
        <p class="text-sm text-gray-700">
        {!! 
            sprintf( 
                __( 'You\'re about to test a stagging environment of NexoPOS 4.x. Recents commits(changes) are automatically deployed here. There might be bugs, issues and you\'re invited to <a class="text-blue-500 hover:underline" target="_blank" href="%s">let us know or just to share your impressions</a>' ),
                'https://github.com/Blair2004/NexoPOS-4x/issues/new'
            ) 
        !!}
        </p>
        <ul class="text-gray-600">
            <li><span class="font-bold">Username</span> : admin</li>
            <li><span class="font-bold">Password</span> 123456</li>
        </ul>
    </div>
</div>