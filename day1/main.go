package main

import (
	"bufio"
	"fmt"
	"os"
	"strconv"
)

func main() {

	readFile, err := os.Open("input.txt")

	if err != nil {
		fmt.Println(err)
		return
	}
	fileScanner := bufio.NewScanner(readFile)
	fileScanner.Split(bufio.ScanLines)

	var numbers []int
	for fileScanner.Scan() {
		num := readCalibrationLine(fileScanner.Bytes())
		if num == 0 {
			fmt.Println("Invalid line supplied: ", fileScanner.Text())
		}
		numbers = append(numbers, num)
	}

	readFile.Close()

	sum := 0
	for _, num := range numbers {
		sum += num
	}
	fmt.Println("Output =", sum)
}

func readCalibrationLine(line []byte) int {
	var firstNumber byte
	var lastNumber byte
	firstNumberSet := false
	for _, b := range line {
		if !isNumber(b) {
			continue
		}
		lastNumber = b
		if !firstNumberSet {
			firstNumber = b
			firstNumberSet = true
		}
	}

	if firstNumber == byte(0) || lastNumber == byte(0) {
		fmt.Println("You must supply two numbers")
		return 0
	}

	concatenated := []byte{firstNumber}
	concatenated = append(concatenated, lastNumber)

	num, err := strconv.Atoi(string(concatenated))
	if err != nil {
		fmt.Println("Error converting to int:", err)
		return 0
	}
	return num
}

func isNumber(b byte) bool {
	return b >= '0' && b <= '9'
}
