<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/**
 * @OA\Info(
 *     title="Game Results API",
 *     version="1.0.0",
 *     description="API for managing game results"
 * )
 */
class ResultsController extends Controller
{

    /**
     * @OA\Post(
     *     path="/results",
     *     tags={"results"},
     *     summary="Store a new result",
     *     description="Stores a new gaming result with optional member association.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"milliseconds"},
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="milliseconds", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Result stored successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'milliseconds' => 'required|numeric',
        ]);

        $memberId = null;
        if ($request->has('email')) {
            $member = Member::firstOrCreate(['email' => $request->email]);
            $memberId = $member->id;
        }

        Result::create([
            'member_id' => $memberId,
            'milliseconds' => $request->milliseconds,
        ]);

        return response()->json(null, 204);
    }
    /**
     * @OA\Get(
     *     path="/top-results",
     *     tags={"results"},
     *     summary="Get top results",
     *     description="Retrieves the top gaming results with optional personal result for a specific member.",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=false,
     *         description="Email of the member to get personal result.",
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Top results retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="top",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="email", type="string", description="Masked email of the member."),
     *                     @OA\Property(property="milliseconds", type="integer", description="Best time in milliseconds.")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="self",
     *                 type="object",
     *                 nullable=true,
     *                 @OA\Property(property="email", type="string", description="Email of the requesting member."),
     *                 @OA\Property(property="milliseconds", type="integer", nullable=true, description="Personal best time in milliseconds."),
     *                 @OA\Property(property="place", type="integer", nullable=true, description="Personal place in the top results.")
     *             )
     *         )
     *     )
     * )
     */
    public function topResults(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email'
        ]);

        // Получение топ-10 результатов
        $topResults = Result::with('member')
            ->whereNotNull('member_id')
            ->select('member_id', DB::raw('MIN(milliseconds) as best_time'))
            ->groupBy('member_id')
            ->orderBy('best_time')
            ->take(10)
            ->get();

        $data = [
            'top' => $topResults->map(function ($result) {
                return [
                    'email' => $this->maskEmail($result->member->email),
                    'milliseconds' => $result->best_time
                ];
            })
        ];

        // Получение лучшего результата для данного пользователя
        if ($request->has('email')) {
            $selfResult = Result::with('member')
                ->whereHas('member', function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->orderBy('milliseconds')
                ->first();

            $data['self'] = [
                'email' => $request->email,
                'milliseconds' => $selfResult?->milliseconds,
                // Вычисление места пользователя
                'place' => $selfResult ? $this->calculatePlace($selfResult->milliseconds, $topResults) : null
            ];
        }
        Log::info('Top results data:', ['data' => $data]);
        return response()->json(['data' => $data]);
    }

    private function maskEmail($email)
    {
        return substr($email, 0, 2) . '******' . strstr($email, '@');
    }

    private function calculatePlace($milliseconds, $topResults)
    {
        foreach ($topResults as $index => $result) {
            if ($result->milliseconds == $milliseconds) {
                return $index + 1;
            }
        }
        return null;
    }

}
