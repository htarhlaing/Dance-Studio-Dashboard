<?php $__env->startSection('title', 'Availability'); ?>

<?php $__env->startPush('styles'); ?>
        <style>
            .cell-hint { color: #6b7280; font-size: 12px; margin-left: 8px; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Availability</h1>
                <div class="muted">Business hours: <?php echo e($businessStartTime); ?>-<?php echo e($businessEndTime); ?></div>
                <div class="muted">Private lesson duration: <?php echo e($resolvedDurationMinutes); ?> minutes</div>
                <div class="muted">Booking interval: <?php echo e($bookingIntervalMinutes); ?> minutes</div>
            </div>
            <div class="page-actions">
                <a class="btn btn-secondary" href="<?php echo e(url('/regular-classes')); ?>">Manage Regular Classes</a>
            </div>
        </div>

        <form method="get" action="<?php echo e(url('/availability')); ?>">
            <div class="form-row">
                <div>
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="<?php echo e($date); ?>">
                </div>
                <div>
                    <label for="duration">Duration</label>
                    <select id="duration" name="duration">
                        <?php
                            $selectedDuration = $duration ?? $resolvedDurationMinutes;
                        ?>
                        <?php $__currentLoopData = [30, 60, 90, 120]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $minutes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($minutes); ?>" <?php if($selectedDuration === $minutes): echo 'selected'; endif; ?>><?php echo e($minutes); ?> minutes</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <input type="hidden" name="studio_id" value="<?php echo e($studioId); ?>">
                <div>
                    <button class="btn" type="submit">Search</button>
                </div>
            </div>
        </form>

        <?php if(count($rooms) === 0): ?>
            <p class="muted">No rooms found for this studio.</p>
        <?php elseif(count($availability) === 0): ?>
            <p class="muted">No records found.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Time</th>
                            <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th><?php echo e($room->name); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $availability; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($slot['time']); ?></td>
                                <?php
                                    $byRoomId = collect($slot['rooms'])->keyBy('room_id');
                                ?>
                                <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $cell = $byRoomId->get($room->id);
                                        $status = $cell['status'] ?? 'available';
                                        $reason = $cell['reason'] ?? null;
                                        $statusClass = 'status-'.$status;
                                        $statusLabel = match ($status) {
                                            'available' => 'Available',
                                            'regular' => 'Regular Class',
                                            'private_booked' => 'Booked',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled',
                                            default => $status,
                                        };
                                    ?>
                                    <td>
                                        <span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                                        <?php if($reason): ?>
                                            <span class="cell-hint"><?php echo e($reason); ?></span>
                                        <?php endif; ?>
                                        <?php if($status === 'available' && $canCreateBooking): ?>
                                            <?php
                                                [$startTime, $endTime] = explode('-', $slot['time']);
                                                $activeDuration = $duration ?? $resolvedDurationMinutes;
                                                $query = http_build_query([
                                                    'date' => $date,
                                                    'room_id' => $room->id,
                                                    'start_time' => $startTime,
                                                    'end_time' => $endTime,
                                                    'duration' => $activeDuration,
                                                ]);
                                            ?>
                                            <a class="btn btn-small btn-book" href="<?php echo e(url('/private-bookings/create')); ?>?<?php echo e($query); ?>">Book</a>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/availability.blade.php ENDPATH**/ ?>