<?php
namespace App\Providers;
use App\Models\Brief;
use App\Models\Chats\Message;
use App\Models\Deal;
use App\Models\User;
use App\Observers\BriefObserver;
use App\Observers\DealObserver;
use App\Observers\MessageObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Registered::class => [
        //     SendEmailVerificationNotification::class,
        // ],
    ];
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Message::observe(MessageObserver::class);
        Deal::observe(DealObserver::class);
        Brief::observe(BriefObserver::class);
    }
    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
