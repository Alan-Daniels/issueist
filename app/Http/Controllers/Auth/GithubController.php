<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\RequestOptions;
use function Illuminate\Log\log;

//Route::get('/auth/redirect', function () {
//    return Socialite::driver('github')->redirect();
//})->name("auth/redirect");
//
//Route::get('/auth/callback', function () {
//    $user = Socialite::driver('github')->user();
//
//    // $user->token
//});

class GithubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->setScopes(['read:user', 'public_repo'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        $access_code = $request->get("code");
        $reported_state = $request->get("state");
        $saved_state = $request->session()->get("state");
        if ($reported_state !== $saved_state) {
            // The sketch is real with this request
            //return abort(401);
        }

        // https://docs.github.com/en/apps/creating-github-apps/authenticating-with-a-github-app/generating-a-user-access-token-for-a-github-app#using-the-web-application-flow-to-generate-a-user-access-token

        $access_token = Http::withOptions([
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ])->bodyFormat(RequestOptions::JSON)->post("https://github.com/login/oauth/access_token",[
                'client_id' => env("GITHUB_CLIENT_ID"),
                'client_secret' => env('GITHUB_SECRET'),
                'code' => $access_code,
                //'redirect_uri' => env("GITHUB_CALLBACK_URI"),
        ]);
        if (!$access_token->ok()) {
            log("failed :(", [ "body" => $access_token->body() ]);
            return abort(500);
        }
        $token = $access_token->json();
        if (isset($token['error'])) {
            log("failed :(", [ "json" => $token ]);
            return abort(500);
        }
        log("got token", ["token" => $token]);
        $authorisation = ucfirst($token['token_type']) . ' ' . $token['access_token'];

        $githubRequestUser = Http::withOptions([
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github+json',
                'Authorization' => $authorisation,
            ],
            RequestOptions::DEBUG => true,
        ])->get('https://api.github.com/user', null);
        $githubUser = $githubRequestUser->json();
        log("githubUser ", $githubUser);

        $user = User::updateOrCreate([
            'github_id' => $githubUser['id'],
        ], [
            'name' => $githubUser['name'],
            //'email' => $githubUser['email'],
            'github_token' => $token['access_token'],
            'github_refresh_token' => $token['refresh_token'],
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }

    protected const Filters = [
        "assigned", "created", "mentioned", "subscribed", "repos", "all",
    ];
    protected const States = [
        "open", "closed", "all",
    ];

    protected function Headers() {
        return [
            'Accept' => 'application/vnd.github.raw+json',
            'Authorization' => "Bearer " . env('GITHUB_TOKEN'),
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
    }

    public function QueryRepoIssues(Request $request)
    {
        $per_page = max(0, min(100, $request->integer("per_page", 30)));
        $page = max(1, $request->integer("page"));

        $filter = $request->string("filter", "all");
        if (!in_array($filter, self::Filters)) {
            $filter = "all";
        }
        $state = $request->string("state", "all");
        if (!in_array($state, self::States)) {
            $state = "all";
        }

        $resp = Http::withHeaders($this->Headers())->get("https://api.github.com/issues", [
            'filter' => $filter,
            'state' => $state,
            'per_page' => $per_page,
            'page' => $page,
        ]);
        return $resp->json();
    }

    public function GetRepoIssue(string $owner, string $repo, string $issue_number)
    {
        $resp = Http::withHeaders($this->Headers())->get("https://api.github.com/repos/{$owner}/{$repo}/issues/{$issue_number}", []);
        return $resp->json();
    }
}
