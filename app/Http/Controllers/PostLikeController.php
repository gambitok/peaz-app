<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostLikeController
{

    public function getLikesGroupedByCuisines(Request $request)
    {
        // Отримуємо user_id з GET-запиту
        $userId = $request->input('user_id');

        if (!$userId) {
            // Якщо user_id не передано в GET-запиті
            return response()->json([
                'status' => 'error',
                'message' => 'User ID is required'
            ], 400); // Статус 400 для помилки валідації
        }

        // Виконуємо SQL-запит для отримання даних, в тому числі повної інформації про пости
        $likesGrouped = DB::table('postlikes')
            ->join('posts', 'postlikes.post_id', '=', 'posts.id') // З'єднуємо таблиці
            ->select(
                'posts.cuisines', // Категорія кухні
                DB::raw('GROUP_CONCAT(postlikes.post_id) AS post_ids'), // Агрегуємо всі post_id
                DB::raw('COUNT(posts.id) AS totalCount') // Кількість постів у кожній категорії
            )
            ->where('postlikes.user_id', $userId) // Фільтруємо по user_id
            ->groupBy('posts.cuisines') // Групуємо по cuisines
            ->get(); // Отримуємо результат запиту

        // Якщо знайдено хоча б одну категорію кухні
        if ($likesGrouped->isNotEmpty()) {
            // Для кожної категорії кухні отримуємо відповідні пости
            foreach ($likesGrouped as $group) {
                // Отримуємо всі пости для поточної категорії
                $posts = DB::table('posts')
                    ->whereIn('id', explode(',', $group->post_ids)) // Вибираємо пости по post_id
                    ->get();

                // Додаємо пости до кожної категорії
                $group->posts = $posts;
            }

            // Повертаємо успішну відповідь з даними
            return response()->json([
                'status' => 'success',
                'message' => 'Liked posts grouped by cuisines with post details fetched successfully',
                'data' => $likesGrouped
            ], 200);
        } else {
            // Якщо дані не знайдені
            return response()->json([
                'status' => 'error',
                'message' => 'No liked posts found'
            ], 404);
        }
    }

}
