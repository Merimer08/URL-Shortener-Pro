<?php
// app/Models/Click.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Click extends Model
{
    use HasFactory;

    protected $fillable = ['link_id','clicked_at','ip','user_agent','browser','country']; // 👈 añade link_id

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
