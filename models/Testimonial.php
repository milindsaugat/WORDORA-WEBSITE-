<?php
/**
 * WORDORA — Testimonial Model
 */
class Testimonial {
    public static function getActive(): array {
        return DB::getInstance()->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
    }
    public static function getAll(): array {
        return DB::getInstance()->query("SELECT * FROM testimonials ORDER BY sort_order ASC")->fetchAll();
    }
}
