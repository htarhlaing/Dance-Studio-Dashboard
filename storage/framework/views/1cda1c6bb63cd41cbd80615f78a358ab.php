<?php $__env->startSection('title', 'Availability'); ?>

<?php $__env->startPush('styles'); ?>
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; vertical-align: top; }
            th { background: #f8fafc; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; color: #fff; }
            .available { background: #198754; }
            .regular { background: #0d6efd; }
            .private_booked { background: #fd7e14; }
            .completed { background: #6b7280; }
            .muted { color: #6b7280; font-size: 12px; margin-left: 8px; }
            .row { display: flex; gap: 12px; align-items: end; margin-bottom: 16px; flex-wrap: wrap; }
            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; }
            select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; }
            button { padding: 7px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 6px; cursor: pointer; }
            .book-link { display: inline-block; margin-left: 10px; padding: 2px 8px; border: 1px solid #111827; border-radius: 6px; color: #111827; text-decoration: none; font-size: 12px; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <h1>Availability</h1>
        <p class="muted">Business hours: <?php echo e($businessStartTime); ?>-<?php echo e($businessEndTime); ?></p>
        <p class="muted">Private lesson duration: <?php echo e($resolvedDurationMinutes); ?> minutes</p>
        <p class="muted">Booking interval: <?php echo e($bookingIntervalMinutes); ?> minutes</p>

        <form method="get" action="<?php echo e(url('/availability')); ?>">
            <div class="row">
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
                    <button type="submit">Search</button>
                </div>
            </div>
        </form>

        <?php if(count($rooms) === 0): ?>
            <p class="muted">No rooms found for this studio.</p>
        <?php else: ?>
            <table>
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
                                ?>
                                <td>
                                    <span class="badge <?php echo e($status); ?>"><?php echo e($status); ?></span>
                                    <?php if($reason): ?>
                                        <span class="muted"><?php echo e($reason); ?></span>
                                    <?php endif; ?>
                                    <?php if($status === 'available'): ?>
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
                                        <a class="book-link" href="<?php echo e(url('/private-bookings/create')); ?>?<?php echo e($query); ?>">Book</a>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/availability.blade.php ENDPATH**/ ?>