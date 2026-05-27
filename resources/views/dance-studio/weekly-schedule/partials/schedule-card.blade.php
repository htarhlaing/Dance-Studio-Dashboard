@php
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
@endphp

<div class="schedule-card schedule-card-{{ $type !== '' ? $type : 'item' }}">
    <div class="schedule-card-header">
        <span class="schedule-badge schedule-badge-{{ $type !== '' ? $type : 'item' }}">{{ $badge }}</span>
        <div class="schedule-title">{{ $title }}</div>
    </div>
    <div class="schedule-line">{{ $roomName }}@if ($timeLabel !== '') · {{ $timeLabel }}@endif</div>
    <div class="schedule-meta">Teacher: {{ $teacherLabel }}</div>
    @if ($type === 'private')
        <div class="schedule-meta">Student: {{ $studentLabel }}</div>
        <div class="schedule-meta">Status: {{ $status !== '' ? $status : '-' }}</div>
    @endif
</div>

