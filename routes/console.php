<?php

use App\Models\Render;
use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune', ['--model' => [Render::class]])->daily();
