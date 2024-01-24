<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Result;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ResultsTableSeeder extends Seeder
{
    use DatabaseTransactions;
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $members = Member::pluck('id')->toArray();
        $domains = ['gmail.com', 'yahoo.com', 'mail.com', 'outlook.com'];

        for ($i = 0; $i < 10000; $i++) {
            $memberId = $members[array_rand($members)];
            $milliseconds = rand(1000, 15000);

            Result::create([
                'member_id' => $memberId,
                'milliseconds' => $milliseconds
            ]);
        }
    }
}
