<x-app-layout>
    <div class="container mx-auto my-2 pb-6 flex">
        <h1 class="text-3xl flex-auto">{{$issue["title"]}} <span class="text-slate-500">#{{$issue["issue_id"]["issue_number"]}}</span></h1>
        <div class="">
            @if ($issue["state"] == "open")
            <span class="bg-green-100 border-green-500 border-l-4 w-fit py-2 px-3 rounded-lg">
                <span class="text-xs font-semibold">open</span>
            </span>
            @else
            <span class="bg-slate-200 border-slate-500 border-l-4 w-fit py-2 px-3 rounded-lg">
                <span class="text-xs font-semibold">closed</span>
            </span>
            @endif
        </div>
    </div>
    <div class="px-5 flex">
        <section class="flex py-3 pr-2 flex-auto">
            <img src="{{$issue["issuer"]["avatar_url"]}}" alt="{{$issue["issuer"]["login"]}}" class="w-12 h-12 rounded-full mx-3 shadow">
            <div class="rounded border-blue-500 border w-full">
                <p class="p-2 bg-blue-200 border-blue-500 border-b"><strong>{{$issue["issuer"]["login"]}}</strong> opened issue in <a href="{{$issue["issue_id"]["html_url"]}}" class="text-[#6C3082] hover:text-blue-700 hover:underline duration-200">
                    {{$issue["issue_id"]["owner"]}}/{{$issue["issue_id"]["repo"]}}
                </a>
                <span class="text-xs text-slate-500 ml-2">opened {{$issue["created_at"]->format("Y-m-d, g:i a")}}</span>
                </p>
                <div class="p-2">{!! $issue["body"] !!}</div>
            </div>
        </section>

        <section class="px-5">
            <h2 class="text-slate-500">Assignees</h2>
            @foreach ($issue["assignees"] as $assignee)
            <p class="hover:bg-blue-200 duration-200 rounded py-1 pl-1 pr-5">
                <img src="{{$assignee["avatar_url"]}}" alt="{{$assignee["login"]}}" class="w-5 h-5 rounded-full inline mr-1">
                <span>{{$assignee["login"]}}</span>
            </p>
            @endforeach
        </section>
    </div>
    {{--<pre>{{json_encode($issue, JSON_PRETTY_PRINT)}}</pre>--}}
</x-app-layout>
