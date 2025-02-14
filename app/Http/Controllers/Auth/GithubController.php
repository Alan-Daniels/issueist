<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;

class GithubController extends Controller
{
    private const Filters = [
        "assigned",
        "created",
        "mentioned",
        "subscribed",
        "repos",
        "all",
    ];
    private const States = [
        "open",
        "closed",
        "all",
    ];

    private function Authorization(Request $request): ?string
    {
        if (!empty(env('GITHUB_PERSONAL_TOKEN'))) {
            return "Bearer " . env("GITHUB_PERSONAL_TOKEN");
        } else {
            return null;
        }
    }

    /**
     * @return array<string,string>
     */
    private function Headers(string $authorization): array
    {
        return [
            //'Time-Zone' => date_default_timezone_get(), // doesn't work for these endpoints ¯\_(ツ)_/¯
            'Accept' => 'application/vnd.github.html+json',
            'Authorization' => $authorization,
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
    }

    public function QueryRepoIssues(Request $request): View
    {
        $per_page = max(0, min(100, $request->integer("per_page", 30)));
        $page = max(1, $request->integer("page"));

        $filter = $request->string("filter", null);
        if (!in_array($filter, self::Filters)) {
            $filter = null;
        }
        $state = $request->string("state", null);
        if (!in_array($state, self::States)) {
            $state = null;
        }

        $authorization = $this->Authorization($request);
        $authorized = $authorization !== null;

        $issues = [];
        $error = null;
        $pagination = [];
        if ($authorized) {
            $resp = Http::withHeaders($this->Headers($authorization))->get("https://api.github.com/issues", [
                'filter' => $filter,
                'state' => $state,
                'per_page' => $per_page,
                'page' => $page,
            ]);
            $pagination = self::PaginationLinks($resp->header("link"));
            $issues = $resp->json();
            if (isset($issues['message'])) {
                $error = $issues['message'];
                $issues = [];
            }
        }

        $mapped_issues = array_map([self::class, "IssueInfo"], $issues);
        return view("issues", [
            "issues" => $mapped_issues,
            "error" => $error,
            "filters" => self::Filters,
            "states" => self::States,
            "query" => [
                "filter" => $filter,
                "state" => $state,
                "per_page" => $per_page,
                "page" => $page,
            ],
            "pagination" => $pagination,
            "authorized" => $authorized,
        ]);
    }

    public function GetRepoIssue(Request $request, string $owner, string $repo, string $issue_number): View
    {
        $authorization = $this->Authorization($request);
        $authorized = $authorization !== null;

        $mapped_issue  = null;
        $error = null;

        if ($authorized) {
            $resp = Http::withHeaders($this->Headers($authorization))
                ->get("https://api.github.com/repos/{$owner}/{$repo}/issues/{$issue_number}", []);
            $issue = $resp->json();
            if (isset($issue['error'])) {
                $error = $issue['error'];
            } else {
                $mapped_issue = self::IssueInfo($issue);
            }
        }

        return view("issue", [
            "issue" => $mapped_issue,
            "error" => $error,
            "authorized" => $authorized,
        ]);
    }

    /**
     * @return array<string,string>
     */
    private static function PaginationLinks(string $paginationHeader): array
    {
        if (empty($paginationHeader)) {
            return [];
        }

        $pagination = [];
        foreach (explode(", ", $paginationHeader) as $link) {
            $queryStart = strpos($link, "?") + 1;
            $queryEnd = strpos($link, ">");
            $query = substr($link, $queryStart, $queryEnd - $queryStart);

            $nameStart = strpos($link, "rel=\"") + 5;
            $nameEnd = strpos($link, "\"", $nameStart);
            $name = substr($link, $nameStart, $nameEnd - $nameStart);

            $pagination[$name] = URL::to("/issues?{$query}");
        }

        return $pagination;
    }

    /**
     * @return array<string,mixed>
     */
    private static function IssueInfo(array $githubIssue): array
    {
        $TZ = new \DateTimeZone(date_default_timezone_get());

        // https://api.github.com/repos/{owner}/{repo}/issues/{issue_number}
        $issue_url = $githubIssue["url"];
        [$owner, $repo, $_, $issue_number] = explode("/", str_replace('https://api.github.com/repos/', '', $issue_url), 4);
        $html_url = $githubIssue["html_url"];

        $title = $githubIssue["title"];
        $body = $githubIssue["body_html"];
        $state = $githubIssue["state"];

        $created_at = new \DateTime($githubIssue["created_at"]);
        $created_at->setTimezone($TZ);

        $assignees = $githubIssue['assignees'];
        $issuer = $githubIssue['user'];

        return [
            "issue_id" => [
                "owner" => $owner,
                "repo" => $repo,
                "issue_number" => $issue_number,
                "uri" => "/issues/{$owner}/{$repo}/{$issue_number}",
                "html_url" => $html_url,
            ],
            "title" => $title,
            "body" => $body,
            "state" => $state,
            "created_at" => $created_at,
            "issuer" => $issuer,
            "assignees" => $assignees,
        ];
    }
}
