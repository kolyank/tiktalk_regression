library(nlme)

setwd("/tmp")

args <- commandArgs(TRUE)
param_patient_age <- as.integer(args[1])
param_patient_gender <- as.integer(args[2])
param_treatment_complexity <- as.integer(args[3])
param_treatment_phases_count <- as.integer(args[4])

load("nonlinear_regression_model.rda")

newdata <- data.frame(patient_age=c(param_patient_age),patient_gender=c(param_patient_gender),treatment_complexity=c(param_treatment_complexity),treatment_phases_count=c(param_treatment_phases_count))
newdata$treatment_duration <- predict(reg, newdata)
tail(newdata, n = 1)
