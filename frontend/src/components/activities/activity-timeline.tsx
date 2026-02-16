'use client';

import React, { useEffect, useState } from 'react';
import { Phone, Mail, Calendar, CheckSquare, StickyNote, ArrowRightLeft } from 'lucide-react';
import { activitiesApi } from '@/lib/api/activities';
import type { Activity, ActivityType } from '@/types';
import { formatRelativeTime } from '@/lib/utils/format';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils/cn';

interface ActivityTimelineProps {
  activitableType: string;
  activitableId: number;
}

const activityIcons: Record<ActivityType, React.ReactNode> = {
  call: <Phone className="h-4 w-4" />,
  email: <Mail className="h-4 w-4" />,
  meeting: <Calendar className="h-4 w-4" />,
  task: <CheckSquare className="h-4 w-4" />,
  note: <StickyNote className="h-4 w-4" />,
  status_change: <ArrowRightLeft className="h-4 w-4" />,
};

const activityColors: Record<ActivityType, string> = {
  call: 'bg-green-100 text-green-600',
  email: 'bg-blue-100 text-blue-600',
  meeting: 'bg-purple-100 text-purple-600',
  task: 'bg-orange-100 text-orange-600',
  note: 'bg-yellow-100 text-yellow-600',
  status_change: 'bg-gray-100 text-gray-600',
};

function ActivityTimeline({ activitableType, activitableId }: ActivityTimelineProps) {
  const [activities, setActivities] = useState<Activity[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchActivities = async () => {
      try {
        const response = await activitiesApi.list({
          activitable_type: activitableType,
          activitable_id: activitableId,
        });
        setActivities(response.data.data);
      } catch {
        // Silently handle
      } finally {
        setLoading(false);
      }
    };

    fetchActivities();
  }, [activitableType, activitableId]);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-8">
        <Spinner />
      </div>
    );
  }

  if (activities.length === 0) {
    return (
      <p className="py-8 text-center text-sm text-gray-500">
        No activities recorded yet.
      </p>
    );
  }

  return (
    <div className="space-y-0">
      {activities.map((activity, index) => (
        <div key={activity.id} className="relative flex gap-4 pb-6">
          {index < activities.length - 1 && (
            <div className="absolute left-5 top-10 h-full w-px bg-gray-200" />
          )}
          <div
            className={cn(
              'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
              activityColors[activity.type]
            )}
          >
            {activityIcons[activity.type]}
          </div>
          <div className="flex-1 pt-1">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-sm font-medium text-gray-900">
                  {activity.subject}
                </p>
                {activity.description && (
                  <p className="mt-1 text-sm text-gray-600">
                    {activity.description}
                  </p>
                )}
              </div>
              <span className="shrink-0 text-xs text-gray-400">
                {formatRelativeTime(activity.occurred_at || activity.created_at)}
              </span>
            </div>
            {activity.user && (
              <p className="mt-1 text-xs text-gray-400">
                by {activity.user.name}
              </p>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}

export { ActivityTimeline };
