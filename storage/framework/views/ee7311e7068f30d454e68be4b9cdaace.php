<?php
    $type = (string) ($item['type'] ?? '');
    $badge = (string) ($item['badge'] ?? ($type !== '' ? ucfirst($type) : ''));
    $title = (string) ($item['title'] ?? '-');
    $roomName = (string) ($item['room_name'] ?? '-');
    $teacherName = (string) ($item['teacher_name'] ?? '');
    $teacherLabel = $teacherName !== '' ? $teacherName : 'No teacher';
    $studentName = (string) ($item['student_name'] ?? '');
    $studentLabel = $studentName !== '' ? $studentName : 'No student';
    $start = (string) ($item['start_time'] ?? '');
    $end = (string) ($item['end_time'] ?? '');
    $timeLabel = trim($start.' - '.$end);
    $status = (string) ($item['status'] ?? '');
?>

<div class="schedule-card schedule-card-<?php echo e($type !== '' ? $type : 'item'); ?>">
    <div class="schedule-card-header">
        <span class="schedule-badge schedule-badge-<?php echo e($type !== '' ? $type : 'item'); ?>"><?php echo e($badge); ?></span>
        <div class="schedule-title"><?php echo e($title); ?></div>
    </div>
    <div class="schedule-line"><?php echo e($roomName); ?><?php if($timeLabel !== ''): ?> · <?php echo e($timeLabel); ?><?php endif; ?></div>
    <div class="schedule-meta">Teacher: <?php echo e($teacherLabel); ?></div>
    <?php if($type === 'private'): ?>
        <div class="schedule-meta">Student: <?php echo e($studentLabel); ?></div>
        <div class="schedule-meta">Status: <?php echo e($status !== '' ? $status : '-'); ?></div>
    <?php endif; ?>
</div>

<?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/weekly-schedule/partials/schedule-card.blade.php ENDPATH**/ ?>