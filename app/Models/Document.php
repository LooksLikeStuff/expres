<?php
// app/Models/Document.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Document extends Model
{
    use SoftDeletes;
    
    protected $table = 'documents';
    
    protected $fillable = [
        'deal_id',
        'user_id',
        'path',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'description',
    ];
    
    /**
     * Связь с сделкой
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
    
    /**
     * Связь с пользователем, загрузившим документ
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Получить URL документа
     */
    public function getUrlAttribute()
    {
        return url('storage/' . $this->path);
    }
    
    /**
     * Получить класс иконки для документа
     */
    public function getIconClassAttribute()
    {
        $extension = strtolower($this->extension);
        
        switch ($extension) {
            case 'pdf':
                return 'fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fa-file-word';
            case 'xls':
            case 'xlsx':
                return 'fa-file-excel';
            case 'ppt':
            case 'pptx':
                return 'fa-file-powerpoint';
            case 'zip':
            case 'rar':
            case '7z':
                return 'fa-file-archive';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
            case 'svg':
            case 'bmp':
                return 'fa-file-image';
            case 'txt':
                return 'fa-file-alt';
            case 'mp4':
            case 'avi':
            case 'mov':
            case 'wmv':
                return 'fa-file-video';
            case 'mp3':
            case 'wav':
            case 'ogg':
                return 'fa-file-audio';
            default:
                return 'fa-file';
        }
    }
}
