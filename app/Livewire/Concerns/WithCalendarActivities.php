<?php

namespace App\Livewire\Concerns;

use App\Models\AgriculturalActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait WithCalendarActivities
{
    public function getActivitiesForMonth(): \Illuminate\Support\Collection
    {
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = AgriculturalActivity::forViticulturist(Auth::id())
            ->whereBetween('activity_date', [$startDate, $endDate])
            ->with(['plot', 'phytosanitaryTreatment.product', 'fertilization',
                'irrigation', 'culturalWork', 'observation']);

        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }
        if ($this->activityType) {
            $query->ofType($this->activityType);
        }

        return $query->get()->groupBy(fn ($a) => Carbon::parse($a->activity_date)->format('Y-m-d'));
    }

    public function getUpcomingActivities(): \Illuminate\Support\Collection
    {
        $today = Carbon::today();
        $end = $today->copy()->addDays(6)->endOfDay();

        $query = AgriculturalActivity::forViticulturist(Auth::id())
            ->whereBetween('activity_date', [$today, $end])
            ->with(['plot', 'phytosanitaryTreatment.product', 'fertilization',
                'irrigation', 'culturalWork', 'observation'])
            ->orderBy('activity_date');

        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }

        return $query->get();
    }

    public function getRecentActivities(): \Illuminate\Support\Collection
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->subDays(6)->startOfDay();

        $query = AgriculturalActivity::forViticulturist(Auth::id())
            ->whereBetween('activity_date', [$weekStart, $today->copy()->subDay()->endOfDay()])
            ->with(['plot'])
            ->orderByDesc('activity_date')
            ->limit(5);

        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }

        return $query->get();
    }

    public function getCalendarDays(): array
    {
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $startOfCalendar = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endDate->copy()->endOfWeek(Carbon::SUNDAY);

        $activities = $this->getActivitiesForMonth();
        $events = $this->getEventsForPeriod($startOfCalendar, $endOfCalendar);

        $days = [];
        $currentDay = $startOfCalendar->copy();

        while ($currentDay <= $endOfCalendar) {
            $dateKey = $currentDay->format('Y-m-d');
            $dayActivities = $activities->get($dateKey, collect());
            $dayEvents = $events->get($dateKey, collect());

            $days[] = [
                'date' => $currentDay->copy(),
                'dateKey' => $dateKey,
                'day' => $currentDay->day,
                'isCurrentMonth' => $currentDay->month == $this->currentMonth,
                'isToday' => $currentDay->isToday(),
                'activities' => $dayActivities,
                'activityCount' => $dayActivities->count(),
                'events' => $dayEvents,
                'eventCount' => $dayEvents->count(),
                'hasAlerts' => $dayEvents->whereIn('type', ['alert_ropo', 'alert_itb', 'alert_authorization'])->isNotEmpty(),
            ];

            $currentDay->addDay();
        }

        return $days;
    }
}
