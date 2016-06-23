/*jshint node:true */

"use strict";

var combine = require('stream-combiner');

var validateStep = function(step) {
		if(!step) {
			throw new Error("Invalid call to lazypipe().pipe(): no stream creation function specified");
		} else if(typeof step !== 'function') {
			throw new Error("Invalid call to lazypipe().pipe(): argument is not a function.\n" +
				                "    Remember not to call stream creation functions directly! e.g.: pipe(foo), not pipe(foo())");
		}
	},
	validateSteps = function(steps) {
		if(steps.length === 0) {
			throw new Error("Tried to build a pipeline with no pipes!");
		}
	};

function lazypipe() {
	var createPipeline = function(steps) {
		
		var build = function() {
				validateSteps(steps);
				return combine.apply(null, steps.map(function(t) {
					return t.task.apply(null, t.args);
				}));
			};
		
		build.appendStepsTo = function(otherSteps) {
			return otherSteps.concat(steps);
		};
		
		build.pipe = function(step) {
			validateStep(step);
			if(step.appendStepsTo) {
				// avoid creating nested pipelines
				return createPipeline(step.appendStepsTo(steps));
			} else {
				return createPipeline(steps.concat({
					task: step,
					args: Array.prototype.slice.call(arguments, 1)
				}));
			}
		};
		
		return build;
	};
	
	return createPipeline([]);
}

module.exports = lazypipe;