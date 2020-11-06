<?php

namespace Fantom\Job;

/**
 * Usage:
 *
 * $job = new Job()
 * $job->create(UserJob::class)->run()
 * - This job is a abstract class
 * - User extends this class
 * - User creates his Job object
 * - Calls start method w/out option (arguments)
 * - start methods initialises job and store arguments in the job file,
 *   creates job id and uses this job id as file name to store any job
 *   arguments and info
 * - start method shell_execs Fantom's Job Runner with user's job class as
 *   argument passed to it
 * - Now the Job Runner class will create object of user's job class and
 *   call the run method.
 * - User can call getArgv method to get the argument (as object) passed
 *   to this job.
 *
 */

class Job
{

}
