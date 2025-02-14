<x-app-layout>
    <div class="container mx-auto">
        <div class="rounded border-slate-500 border w-full">
            <div class="p-2 bg-slate-200">
                <form action="{{URL::to('/issues')}}" class="flex">
                    <div>
                        <label for="issues_filter" class="block">Filter</label>
                        <select name="filter" id="issues_filter">
                            @foreach ($filters as $filter)
                            <option value="{{$filter}}" @selected($filter == $query["filter"])>{{$filter}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="issues_state" class="block">State</label>
                        <select name="state" id="issues_state">
                            @foreach ($states as $state)
                            <option value="{{$state}}" @selected($state == $query["state"])>{{$state}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-auto"></div>
                    <input type="submit" class="bg-indigo-600 hover:bg-indigo-400 text-white duration-200 rounded h-min p-2 my-auto">
                </form>
            </div>
            @if (!$authorized)
            <div class="p-2 border-t border-slate-400 flex">
                <span class="bg-slate-100 border-slate-500 border-l-4 w-fit py-2 px-3 rounded-lg">
                    <span class="text-xs font-semibold">Error: </span>
                    <span class="text-xs">You are not currently logged in. <br>
                        Set environment variable <strong>GITHUB_PERSONAL_TOKEN</strong> to continue.
                    </span>
                </span>
            </div>
            @elseif ($error !== null)
            <div class="p-2 border-t border-slate-400 flex">
                <span class="bg-red-100 border-red-500 border-l-4 w-fit py-2 px-3 rounded-lg">
                    <span class="text-xs font-semibold">Error: </span>
                    <span class="text-xs">{{$error}}</span>
                </span>
            </div>
            @else
            @foreach ($issues as $issue)
            <div class="p-2 border-t border-slate-400 flex">
                <div class="flex-none w-20">
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
                <div class="flex-auto px-2">
                    <a href="{{URL::to($issue['issue_id']['uri'])}}" class="text-[#6C3082] hover:text-blue-700 hover:underline duration-200">{{$issue["title"]}}</a> <span class="text-slate-500">#{{$issue["issue_id"]["issue_number"]}}</span>
                    <p class="text-xs text-slate-500">
                        opened {{$issue["created_at"]->format("Y-m-d, g:i a")}}
                    </p>
                </div>
                <div class="flex-none">
                    @foreach ($issue["assignees"] as $assignee)
                    <img src="{{$assignee["avatar_url"]}}" alt="{{$assignee["login"]}}" class="w-5 h-5 rounded-full inline mr-1">
                    @endforeach
                </div>
            </div>
            @endforeach
            @endif
            <div class="p-2 bg-slate-200 border-t border-slate-400">
                <div class=" flex mx-auto w-fit bg-blue-200 rounded-md overflow-auto">
                @if (isset($pagination["first"]))
                        <a href="{{$pagination["first"]}}" class="relative px-4 py-2 text-sm font-semibold hover:bg-indigo-500 hover:text-white duration-200">first</a>
                @endif
                @if (isset($pagination["prev"]))
                        <a href="{{$pagination["prev"]}}" class="relative px-4 py-2 text-sm font-semibold hover:bg-indigo-500 hover:text-white duration-200">prev</a>
                @endif

                @if (!empty($pagination))
                    <div aria-current="page" class="bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{$query["page"]}}</div>
                @endif

                @if (isset($pagination["next"]))
                        <a href="{{$pagination["next"]}}" class="relative px-4 py-2 text-sm font-semibold hover:bg-indigo-500 hover:text-white duration-200">next</a>
                @endif
                @if (isset($pagination["last"]))
                        <a href="{{$pagination["last"]}}" class="relative px-4 py-2 text-sm font-semibold hover:bg-indigo-500 hover:text-white duration-200">last</a>
                @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
