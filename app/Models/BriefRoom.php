<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BriefRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'brief_id',
        'key',
        'title',
    ];

    public const DEFAULT_ROOMS = [
        ['key' => 'room_prihod', 'title' => 'Прихожая', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в прихожей? Опишите детали и расстановку мебели.'],
        ['key' => 'room_detskaya', 'title' => 'Детская', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в детской? Опишите детали и расстановку мебели.'],
        ['key' => 'room_kladovaya', 'title' => 'Кладовая', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кладовой? Опишите детали и расстановку мебели.'],
        ['key' => 'room_kukhni_i_gostinaya', 'title' => 'Кухня и гостиная', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кухне и гостиной? Опишите детали и расстановку мебели.'],
        ['key' => 'room_gostevoi_sanuzel', 'title' => 'Гостевой санузел', 'placeholder' => 'Перечислите предпочтения по выбору душа, раковины с тумбой, унитаза и других элементов.'],
        ['key' => 'room_gostinaya', 'title' => 'Гостиная', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в гостиной? Опишите детали и расстановку мебели.'],
        ['key' => 'room_rabocee_mesto', 'title' => 'Рабочее место', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в рабочей зоне? Опишите детали и расстановку мебели.'],
        ['key' => 'room_stolovaya', 'title' => 'Столовая', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в столовой? Опишите детали и расстановку мебели.'],
        ['key' => 'room_vannaya', 'title' => 'Ванная комната', 'placeholder' => 'Укажите предпочтения по выбору ванны/душа, раковины с тумбой, унитаза, полотенцесушителя и стиральной машины.'],
        ['key' => 'room_kukhnya', 'title' => 'Кухня', 'placeholder' => 'Укажите тип плиты, наличие посудомоечной машины, микроволновой печи, духового шкафа, мойки, холодильника и других приборов. Опишите детали и расстановку мебели.'],
        ['key' => 'room_kabinet', 'title' => 'Кабинет', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кабинете? Опишите детали и расстановку мебели.'],
        ['key' => 'room_spalnya', 'title' => 'Спальня', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в спальне? Опишите детали и расстановку мебели.'],
        ['key' => 'room_garderobnaya', 'title' => 'Гардеробная', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в гардеробной? Опишите детали и расстановку мебели.'],
        ['key' => 'room_drugoe', 'title' => 'Другое', 'placeholder' => 'Какие пожелания у вас есть по наполнению в других помещениях? Опишите детали и расстановку мебели.'],
    ];

    protected const CUSTOM_PLACEHOLDER = 'Опишите детали и расстановку мебели для этой комнаты.';

    public function isCustom()
     {
        $defaultKeys = array_column(self::DEFAULT_ROOMS, 'key');
       
        return !in_array($this->key, $defaultKeys, true);
    }

    public function setQuestion(Collection $questions): void
    {
        $type = $this->getQuestionKey();
        
        $this->question = $questions->first(function ($q) use ($type) {
            return $q->key === $type;
        });
    }

    public function placeholder()
    {
        if ($this->isCustom()) {
            return self::CUSTOM_PLACEHOLDER;
        }

        $roomsByKey = array_column(self::DEFAULT_ROOMS, null, 'key');

        return $roomsByKey[$this->key]['placeholder'];
    }

    public function getQuestionKey() 
    {
        //Ключ для получения вопроса
        return $this->isCustom() ? 'room_custom' : 'room_default';
    }
}
